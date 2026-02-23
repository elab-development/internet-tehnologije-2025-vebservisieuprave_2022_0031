<?php

namespace App\Http\Controllers;

use App\Models\Termin;
use Illuminate\Http\Request;
use App\Http\Resources\TerminResource;
use Illuminate\Support\Facades\Validator;

class TerminController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return TerminResource::collection(Termin::all());
    }

    public function create()
    {
        //
    }

    public function mojiTerminiPaginatedFiltered(Request $request)
    {
        $userId = $request->user()->id;
        $perPage = $request->get('per_page', 10);

        $query = Termin::where('korisnik_id', $userId);

        if ($request->filled('tip_dokumenta')) {
            $query->where('tip_dokumenta', $request->get('tip_dokumenta'));
        }

        if ($request->filled('lokacija')) {
            $query->where('lokacija', $request->get('lokacija'));
        }

        if ($request->filled('date_from')) {
            $query->where('datum_vreme', '>=', $request->get('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->where('datum_vreme', '<=', $request->get('date_to'));
        }

        $query->orderByDesc('datum_vreme');
        $paginator = $query->paginate($perPage);

        return TerminResource::collection($paginator);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $minAllowed = now()->addMinutes(30)->toDateTimeString();

        $validator = Validator::make(
            $request->all(),
            [
                'tip_dokumenta' => 'required|string|in:licna_karta,pasos',
                'lokacija' => 'required|string|max:255',

                // najmanje 30 minuta unapred
                'datum_vreme' => [
                    'required',
                    'date',
                    'after:' . $minAllowed,
                ],

                'korisnik_id' => 'required|exists:users,id',
            ],
            [
                'datum_vreme.after' => 'Termin mora biti zakazan najmanje 30 minuta unapred.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validacija nije prosla.',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();

        // Provera da li je termin zauzet (lokacija + datum_vreme)
        $postoji = Termin::where('lokacija', $data['lokacija'])
            ->where('datum_vreme', $data['datum_vreme'])
            ->exists();

        if ($postoji) {
            return response()->json([
                'message' => 'Termin je već zauzet na toj lokaciji i u tom vremenu.'
            ], 409);
        }

        $termin = Termin::create($data);

        // ispravno formiran response (resource + status kod)
        return response()->json(new TerminResource($termin), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        return new TerminResource(Termin::findOrFail($id));
    }

    public function edit(Termin $termin)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $termin = Termin::find($id);
        if (!$termin) {
            return response()->json(['message' => 'Termin nije pronadjen.'], 404);
        }

        $minAllowed = now()->addMinutes(30)->toDateTimeString();

        $validator = Validator::make(
            $request->all(),
            [
                'tip_dokumenta' => 'required|string|in:licna_karta,pasos',
                'lokacija' => 'required|string|max:255',

                // i kod izmene mora biti 30 min unapred
                'datum_vreme' => [
                    'required',
                    'date',
                    'after:' . $minAllowed,
                ],

                'korisnik_id' => 'required|exists:users,id',
            ],
            [
                'datum_vreme.after' => 'Termin mora biti zakazan najmanje 30 minuta unapred.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validacija nije prosla.',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();

        // Provera zauzetosti i kod update (ali ignoriši trenutni termin)
        $postoji = Termin::where('lokacija', $data['lokacija'])
            ->where('datum_vreme', $data['datum_vreme'])
            ->where('id', '!=', $termin->id)
            ->exists();

        if ($postoji) {
            return response()->json([
                'message' => 'Termin je već zauzet na toj lokaciji i u tom vremenu.'
            ], 409);
        }

        $termin->update($data);

        return response()->json(new TerminResource($termin), 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $termin = Termin::find($id);

        if (!$termin) {
            return response()->json(['message' => 'Termin nije pronadjen.'], 404);
        }

        $termin->delete();

        return response()->json(['message' => 'Termin je obrisan.'], 200);
    }
}

