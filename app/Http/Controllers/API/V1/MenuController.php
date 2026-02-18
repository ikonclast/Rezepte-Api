<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMenuRequest;
use App\Http\Requests\UpdateMenuRequest;
use App\Http\Resources\MenuListResource;
use App\Models\Menu;
use App\Models\Recipe;
use App\Services\MenuScalingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;



class MenuController extends Controller
{
    // GET /api/menus
    public function index(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'search'         => 'nullable|string|max:100',
            'page'           => 'nullable|integer|min:1',
            'limit'          => 'nullable|integer|min:1|max:100',
            'order'          => 'nullable|in:recent,title_asc,title_desc',
            'updated_since'  => 'nullable|date',
        ]);

        $limit = $data['limit'] ?? 20;
        $order = $data['order'] ?? 'recent';

        $query = Menu::query()
            ->withCount('recipes')
            ->search($data['search'] ?? null)
            ->updatedSince($data['updated_since'] ?? null);

        //  Zugriffsbeschränkung: Admins sehen alles, andere nur ihre eigenen
        if ($user->role !== 'admin') {
            $query->where('user_id', $user->id);
        }

        //  Sortierung
        switch ($order) {
            case 'title_asc':
                $query->orderBy('title', 'asc');
                break;
            case 'title_desc':
                $query->orderBy('title', 'desc');
                break;
            default: // recent
                $query->orderBy('updated_at', 'desc');
                break;
        }

        //  Pagination
        $paginator = $query->paginate($limit)->appends($request->query());

        return MenuListResource::collection($paginator);
    }

    // POST /api/menus
    public function store(StoreMenuRequest $request)
    {
        $user = $request->user();

    
        if ($user->role !== 'admin') {
            $countForeign = Recipe::whereIn('id', $request->recipes)
                ->where('user_id', '!=', $user->id)
                ->count();
            if ($countForeign > 0) {
                return response()->json([
                    'message' => 'You can only add your own recipes to a menu.'
                ], 403);
            }
        }

        return DB::transaction(function () use ($request, $user) {
            $menu = Menu::create([
                'title'       => $request->title,
                'guest_count' => $request->guest_count,
                'user_id'     => $user->id,
            ]);

            // Reihenfolge gemäß Array-Position
            $attach = [];
            foreach (array_values($request->recipes) as $idx => $rid) {
                $attach[$rid] = ['position' => $idx + 1];
            }
            $menu->recipes()->sync($attach);

            return response()->json([
                'id'          => $menu->id,
                'title'       => $menu->title,
                'guest_count' => $menu->guest_count,
                'recipe_ids'  => array_values($request->recipes),
            ], 201);
        });
    }

    // GET /api/menus/{menu}
    public function show(Request $request, Menu $menu, MenuScalingService $scaler)
    {
        $this->authorize('view', $menu);

        // Defaults
        $defaultGuests = (float)($menu->guest_count ?? 4);
        $overrides = [];

        if ($request->has('pax') || $request->has('recipePax')) {
            $validated = $request->validate([
                'pax' => 'nullable|numeric|min:0.1|max:100000',
                'recipePax' => 'nullable|array',
                'recipePax.*' => 'numeric|min:0.1|max:100000',
            ]);

            if (isset($validated['pax'])) {
                $defaultGuests = (float)$validated['pax'];
            }
            if (isset($validated['recipePax'])) {
                $overrides = array_map('floatval', $validated['recipePax']);
            }

            return response()->json($scaler->buildDetailed($menu, $defaultGuests, $overrides));
        }

        // Standardfall (keine Query-Parameter): nutze menu->guest_count + keine Overrides
        return response()->json($scaler->buildDetailed($menu, $defaultGuests, $overrides));
    }

    // PUT /api/menus/{menu}
    public function update(UpdateMenuRequest $request, Menu $menu)
    {
        $this->authorize('update', $menu);

        // Ownership-Check bei Rezeptänderung
        if ($request->has('recipes') && $request->user()->role !== 'admin') {
            $countForeign = Recipe::whereIn('id', $request->recipes)
                ->where('user_id', '!=', $request->user()->id)
                ->count();
            if ($countForeign > 0) {
                return response()->json([
                    'message' => 'You can only add your own recipes to a menu.'
                ], 403);
            }
        }

        return DB::transaction(function () use ($request, $menu) {
            if ($request->filled('title'))       $menu->title = $request->title;
            if ($request->filled('guest_count')) $menu->guest_count = $request->guest_count;
            $menu->save();

            if ($request->has('recipes')) {
                $attach = [];
                foreach (array_values($request->recipes) as $idx => $rid) {
                    $attach[$rid] = ['position' => $idx + 1];
                }
                $menu->recipes()->sync($attach); // ersetzt komplette Liste
            }

            return response()->json([
                'id'          => $menu->id,
                'title'       => $menu->title,
                'guest_count' => $menu->guest_count,
                'recipe_ids'  => $menu->recipes()->pluck('recipes.id'),
            ]);
        });
    }

    // DELETE /api/menus/{menu}
    public function destroy(Request $request, Menu $menu)
    {
        $this->authorize('delete', $menu);
        $menu->delete();
        return response()->noContent();
    }
}
