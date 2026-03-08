<?php

namespace App\Http\Controllers;

use App\Models\Termin;
use Illuminate\Http\Request;
use App\Http\Resources\TerminResource;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;

class TerminController extends Controller
{
    #[OA\Get(
        path: "/api/termin",
        summary: "Lista svih termina",
        tags: ["Termin"],
        responses: [
            new OA\Response(response: 200, description: "Lista termina")
        ]
    )]
    public function index()
    {
        return TerminResource::collection(Termin::all());
    }

    public function create()
    {
        //
    }

    #[OA\Get(
        path: "/api/termin/moje",
        summary: "Lista termina ulogovanog korisnika sa filterima i paginacijom",
        tags: ["Termin"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "per_page", in: "query", required: false, description: "Broj termina po stranici", schema: new OA\Schema(type: "integer"), example: 10),
            new OA\Parameter(name: "tip_dokumenta", in: "query", required: false, description: "Filter po tipu dokumenta", schema: new OA\Schema(type: "string", enum: ["licna_karta", "pasos"])),
            new OA\Parameter(name: "lokacija", in: "query", required: false, description: "Filter po lokaciji", schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "date_from", in: "query", required: false, description: "Filter od datuma", schema: new OA\Schema(type: "string", format: "date-time")),
            new OA\Parameter(name: "date_to", in: "query", required: false, description: "Filter do datuma", schema: new OA\Schema(type: "string", format: "date-time")),
        ],
        responses: [
            new OA\Response(response: 200, description: "Lista termina korisnika"),
            new OA\Response(response: 401, description: "Unauthorized")
        ]
    )]
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

    #[OA\Post(
        path: "/api/termin",
        summary: "Kreiranje novog termina",
        tags: ["Termin"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["tip_dokumenta", "lokacija", "datum_vreme", "korisnik_id"],
                properties: [
                    new OA\Property(property: "tip_dokumenta", type: "string", enum: ["licna_karta", "pasos"], example: "licna_karta"),
                    new OA\Property(property: "lokacija", type: "string", example: "Beograd - MUP"),
                    new OA\Property(property: "datum_vreme", type: "string", format: "date-time", example: "2025-06-01 10:00:00"),
                    new OA\Property(property: "korisnik_id", type: "integer", example: 1),
                ],
                type: "object"
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Termin uspešno kreiran"),
            new OA\Response(response: 409, description: "Termin je već zauzet"),
            new OA\Response(response: 422, description: "Validaciona greška")
        ]
    )]
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

    #[OA\Get(
        path: "/api/termin/{id}",
        summary: "Prikaz jednog termina",
        tags: ["Termin"],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID termina",
                schema: new OA\Schema(type: "integer"),
                example: 1
            )
        ],
        responses: [
            new OA\Response(response: 200, description: "Podaci o terminu"),
            new OA\Response(response: 404, description: "Termin nije pronađen")
        ]
    )]
    public function show($id)
    {
        return new TerminResource(Termin::findOrFail($id));
    }

    public function edit(Termin $termin)
    {
        //
    }

    #[OA\Put(
        path: "/api/termin/{id}",
        summary: "Ažuriranje termina",
        tags: ["Termin"],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID termina",
                schema: new OA\Schema(type: "integer"),
                example: 1
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["tip_dokumenta", "lokacija", "datum_vreme", "korisnik_id"],
                properties: [
                    new OA\Property(property: "tip_dokumenta", type: "string", enum: ["licna_karta", "pasos"], example: "licna_karta"),
                    new OA\Property(property: "lokacija", type: "string", example: "Beograd - MUP"),
                    new OA\Property(property: "datum_vreme", type: "string", format: "date-time", example: "2025-06-01 10:00:00"),
                    new OA\Property(property: "korisnik_id", type: "integer", example: 1),
                ],
                type: "object"
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Termin uspešno ažuriran"),
            new OA\Response(response: 404, description: "Termin nije pronađen"),
            new OA\Response(response: 409, description: "Termin je već zauzet"),
            new OA\Response(response: 422, description: "Validaciona greška")
        ]
    )]
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

    #[OA\Delete(
        path: "/api/termin/{id}",
        summary: "Brisanje termina",
        tags: ["Termin"],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID termina",
                schema: new OA\Schema(type: "integer"),
                example: 1
            )
        ],
        responses: [
            new OA\Response(response: 200, description: "Termin uspešno obrisan"),
            new OA\Response(response: 404, description: "Termin nije pronađen")
        ]
    )]
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