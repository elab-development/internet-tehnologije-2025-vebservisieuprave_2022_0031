<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use App\Models\Zahtev;
use App\Models\Adresa;
use Illuminate\Http\Request;
use App\Http\Resources\ZahtevResource;
use Illuminate\Support\Facades\Log;
use OpenApi\Attributes as OA;

class ZahtevController extends Controller
{
    #[OA\Get(
        path: "/api/zahtev",
        summary: "Lista svih zahteva",
        tags: ["Zahtev"],
        responses: [
            new OA\Response(response: 200, description: "Lista zahteva")
        ]
    )]
    public function index()
    {
        return ZahtevResource::collection(Zahtev::all());
    }

    public function create()
    {
        //
    }

    #[OA\Get(
        path: "/api/zahtev/moje",
        summary: "Lista zahteva ulogovanog korisnika sa filterima i paginacijom",
        tags: ["Zahtev"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "per_page", in: "query", required: false, description: "Broj zahteva po stranici", schema: new OA\Schema(type: "integer"), example: 10),
            new OA\Parameter(name: "status", in: "query", required: false, description: "Filter po statusu", schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "date_from", in: "query", required: false, description: "Filter od datuma", schema: new OA\Schema(type: "string", format: "date")),
            new OA\Parameter(name: "date_to", in: "query", required: false, description: "Filter do datuma", schema: new OA\Schema(type: "string", format: "date")),
        ],
        responses: [
            new OA\Response(response: 200, description: "Lista zahteva korisnika"),
            new OA\Response(response: 401, description: "Unauthorized")
        ]
    )]
    //FUNKCIJA KOJA VRACA ZAHTEVE
    public function mojiZahteviPaginatedFiltered(Request $request)
    {
        $userId = $request->user()->id;
        $perPage = $request->get('per_page', 10);
        Log::info('ID ulogovanog korisnika: ' . $userId);

        $query = Zahtev::where('korisnik_id', $userId);

        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        if ($request->filled('date_from')) {
            $query->where('datum_kreiranja', '>=', $request->get('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->where('datum_kreiranja', '<=', $request->get('date_to'));
        }

        $query->orderByDesc('datum_kreiranja');
        $paginator = $query->paginate($perPage);

        return ZahtevResource::collection($paginator);
    }

    #[OA\Get(
        path: "/api/zahtev/moje/bracni_status",
        summary: "Lista zahteva tipa bracni_status ulogovanog korisnika sa filterima i paginacijom",
        tags: ["Zahtev"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "per_page", in: "query", required: false, description: "Broj zahteva po stranici", schema: new OA\Schema(type: "integer"), example: 10),
            new OA\Parameter(name: "status", in: "query", required: false, description: "Filter po statusu", schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "date_from", in: "query", required: false, description: "Filter od datuma", schema: new OA\Schema(type: "string", format: "date")),
            new OA\Parameter(name: "date_to", in: "query", required: false, description: "Filter do datuma", schema: new OA\Schema(type: "string", format: "date")),
        ],
        responses: [
            new OA\Response(response: 200, description: "Lista zahteva bracnog statusa korisnika"),
            new OA\Response(response: 401, description: "Unauthorized")
        ]
    )]
    //FUNKCIJA KOJA VRACA ZAHTEV TIPA BRACNI STATUS
    public function mojiBracniStatusPaginatedFiltered(Request $request)
    {
        $userId = $request->user()->id;
        $perPage = $request->get('per_page', 10);
        Log::info('ID ulogovanog korisnika: ' . $userId);

        $query = Zahtev::where('korisnik_id', $userId)
            ->where('tip_zahteva', Zahtev::BRACNI_STATUS);

        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        if ($request->filled('date_from')) {
            $query->where('datum_kreiranja', '>=', $request->get('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->where('datum_kreiranja', '<=', $request->get('date_to'));
        }

        $query->orderByDesc('datum_kreiranja');
        $paginator = $query->paginate($perPage);

        return ZahtevResource::collection($paginator);
    }

    #[OA\Get(
        path: "/api/zahtev/moje/prebivaliste",
        summary: "Lista zahteva tipa prebivaliste ulogovanog korisnika sa filterima i paginacijom",
        tags: ["Zahtev"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "per_page", in: "query", required: false, description: "Broj zahteva po stranici", schema: new OA\Schema(type: "integer"), example: 10),
            new OA\Parameter(name: "status", in: "query", required: false, description: "Filter po statusu", schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "date_from", in: "query", required: false, description: "Filter od datuma", schema: new OA\Schema(type: "string", format: "date")),
            new OA\Parameter(name: "date_to", in: "query", required: false, description: "Filter do datuma", schema: new OA\Schema(type: "string", format: "date")),
        ],
        responses: [
            new OA\Response(response: 200, description: "Lista zahteva prebivalista korisnika"),
            new OA\Response(response: 401, description: "Unauthorized")
        ]
    )]
    //FUNKCIJA KOJA VRACA ZAHTEV ALI SAMO ZA PREVIVALISTE(get/zahtevi/moji)
    public function mojiPromenaPrebivalistaPaginatedFiltered(Request $request)
    {
        $userId = $request->user()->id;
        $perPage = $request->get('per_page', 10);
        Log::info('ID ulogovanog korisnika: ' . $userId);

        $query = Zahtev::where('korisnik_id', $userId)
            ->where('tip_zahteva', Zahtev::PREBIVALISTE);

        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        if ($request->filled('date_from')) {
            $query->where('datum_kreiranja', '>=', $request->get('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->where('datum_kreiranja', '<=', $request->get('date_to'));
        }

        $query->orderByDesc('datum_kreiranja');
        $paginator = $query->paginate($perPage);

        return ZahtevResource::collection($paginator);
    }

    #[OA\Get(
        path: "/api/admin/zahtevi/{id}",
        summary: "Prikaz jednog zahteva sa svim detaljima - samo admin",
        tags: ["Admin"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID zahteva",
                schema: new OA\Schema(type: "integer"),
                example: 1
            )
        ],
        responses: [
            new OA\Response(response: 200, description: "Podaci o zahtevu"),
            new OA\Response(response: 401, description: "Unauthorized"),
            new OA\Response(response: 403, description: "Forbidden - nije admin"),
            new OA\Response(response: 404, description: "Zahtev nije pronađen")
        ]
    )]
    //FUNKCIJA KOJA TACNO TAJ VRACA ZAHTEV  GET /api/admin/zahtevi/{id} - detalji zahteva
    public function prikaziZahtev($id)
    {
    $zahtev = Zahtev::with([
        'korisnik',
        'staraAdresa',
        'novaAdresa',
        'dokumenti'
    ])->findOrFail($id);

    return response()->json($zahtev);
    }

    #[OA\Post(
        path: "/api/zahtev",
        summary: "Kreiranje novog zahteva (prebivaliste ili bracni_status)",
        tags: ["Zahtev"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["tip_zahteva", "broj_licnog_dokumenta", "datum_promene"],
                properties: [
                    new OA\Property(property: "tip_zahteva", type: "string", enum: ["prebivaliste", "bracni_status"], example: "bracni_status"),
                    new OA\Property(property: "broj_licnog_dokumenta", type: "string", example: "123456789"),
                    new OA\Property(property: "datum_promene", type: "string", format: "date", example: "2025-01-01"),
                    new OA\Property(property: "tip_promene", type: "string", enum: ["razvod", "sklapanje_braka"], example: "sklapanje_braka"),
                    new OA\Property(property: "ime_partnera", type: "string", example: "Ana"),
                    new OA\Property(property: "prezime_partnera", type: "string", example: "Anić"),
                    new OA\Property(property: "datum_rodjenja_partnera", type: "string", format: "date", example: "1995-05-10"),
                    new OA\Property(property: "partner_pol", type: "string", enum: ["M", "Z"], example: "Z"),
                    new OA\Property(property: "broj_licnog_dokumenta_partnera", type: "string", example: "987654321"),
                ],
                type: "object"
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Zahtev uspešno kreiran"),
            new OA\Response(response: 401, description: "Unauthorized"),
            new OA\Response(response: 422, description: "Validaciona greška")
        ]
    )]
    //FUNKCIJA KOJA KREIRA ZAHTEV
    public function store(Request $request)
    {
        // mora biti najkasnije juče 
        $maxDatumPromene = now()->subDay()->toDateString();

        $validator = Validator::make($request->all(), [
            'tip_zahteva' => 'required|in:prebivaliste,bracni_status',

            // zajednicka polja (na frontu su obavezna)
            'broj_licnog_dokumenta' => 'required|string|max:255',

            
            'datum_promene' => [
                'required',
                'date',
                'before_or_equal:' . $maxDatumPromene,
            ],

            // bracni_status polja - obavezna samo ako je tip_zahteva bracni_status
            'tip_promene' => 'required_if:tip_zahteva,bracni_status|in:razvod,sklapanje_braka',
            'ime_partnera' => 'required_if:tip_zahteva,bracni_status|string|max:255',
            'prezime_partnera' => 'required_if:tip_zahteva,bracni_status|string|max:255',

            // partner mora biti 18+ 
            'datum_rodjenja_partnera' => [
                'required_if:tip_zahteva,bracni_status',
                'date',
                'before_or_equal:' . now()->subYears(18)->toDateString(),
            ],

            'partner_pol' => 'required_if:tip_zahteva,bracni_status|in:M,Z',
            'broj_licnog_dokumenta_partnera' => 'required_if:tip_zahteva,bracni_status|string|max:255',
        ], [
            'datum_promene.before_or_equal' =>
                'Datum promene mora biti najmanje 1 dan pre današnjeg datuma.',
            'datum_rodjenja_partnera.before_or_equal' =>
                'Partner mora imati najmanje 18 godina.',
            'datum_rodjenja_partnera.required_if' =>
                'Datum rođenja partnera je obavezan.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validacija nije prošla.',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();

        // Backend sam dodaje obavezna polja
        $data['korisnik_id'] = $request->user()->id;
        $data['status'] = 'kreiran';
        $data['datum_kreiranja'] = now();

        Log::info('Podaci za kreiranje zahteva:', $data);

        $zahtev = Zahtev::create($data);

        // AKO JE PROMENA PREBIVALIŠTA – DODAJ ADRESE
        if ($zahtev->tip_zahteva === Zahtev::PREBIVALISTE) {

            // VALIDACIJA STARE I NOVE ADRESE
            $request->validate([
                'stara_adresa.ulica' => 'required|string|max:255',
                'stara_adresa.broj' => 'required|string|max:10',
                'stara_adresa.mesto' => 'required|string|max:255',
                'stara_adresa.opstina' => 'required|string|max:255',
                'stara_adresa.grad' => 'nullable|string|max:255',
                'stara_adresa.postanski_broj' => 'nullable|string|max:10',

                'nova_adresa.ulica' => 'required|string|max:255',
                'nova_adresa.broj' => 'required|string|max:10',
                'nova_adresa.mesto' => 'required|string|max:255',
                'nova_adresa.opstina' => 'required|string|max:255',
                'nova_adresa.grad' => 'nullable|string|max:255',
                'nova_adresa.postanski_broj' => 'nullable|string|max:10',
            ]);

            // STARA ADRESA
            Adresa::create([
                'zahtev_id' => $zahtev->id,
                'ulica' => $request->stara_adresa['ulica'],
                'broj' => $request->stara_adresa['broj'],
                'mesto' => $request->stara_adresa['mesto'],
                'opstina' => $request->stara_adresa['opstina'],
                'grad' => $request->stara_adresa['grad'] ?? '',
                'postanski_broj' => $request->stara_adresa['postanski_broj'] ?? '',
                'trajanje_prebivalista' => 'stalna',
                'uloga_adrese' => 'stara',
            ]);

            // NOVA ADRESA
            Adresa::create([
                'zahtev_id' => $zahtev->id,
                'ulica' => $request->nova_adresa['ulica'],
                'broj' => $request->nova_adresa['broj'],
                'mesto' => $request->nova_adresa['mesto'],
                'opstina' => $request->nova_adresa['opstina'],
                'grad' => $request->nova_adresa['grad'] ?? '',
                'postanski_broj' => $request->nova_adresa['postanski_broj'] ?? '',
                'trajanje_prebivalista' => 'stalna',
                'uloga_adrese' => 'nova',
            ]);
        }

        return response()->json(new ZahtevResource($zahtev), 201);
    }

    #[OA\Get(
        path: "/api/zahtev/{id}",
        summary: "Prikaz jednog zahteva",
        tags: ["Zahtev"],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID zahteva",
                schema: new OA\Schema(type: "integer"),
                example: 1
            )
        ],
        responses: [
            new OA\Response(response: 200, description: "Podaci o zahtevu"),
            new OA\Response(response: 404, description: "Zahtev nije pronađen")
        ]
    )]
    /**
     * Display the specified resource.
     */
    public function show($zahtev_id)
    {
        return new ZahtevResource(Zahtev::findOrFail($zahtev_id));
    }

    public function edit(Zahtev $zahtev)
    {
        //
    }

    #[OA\Put(
        path: "/api/zahtev/{id}",
        summary: "Ažuriranje zahteva",
        tags: ["Zahtev"],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID zahteva",
                schema: new OA\Schema(type: "integer"),
                example: 1
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "tip_zahteva", type: "string", enum: ["prebivaliste", "bracni_status"], example: "bracni_status"),
                    new OA\Property(property: "status", type: "string", example: "kreiran"),
                    new OA\Property(property: "broj_licnog_dokumenta", type: "string", example: "123456789"),
                    new OA\Property(property: "datum_promene", type: "string", format: "date", example: "2025-01-01"),
                    new OA\Property(property: "tip_promene", type: "string", enum: ["razvod", "sklapanje_braka"], example: "razvod"),
                    new OA\Property(property: "ime_partnera", type: "string", example: "Ana"),
                    new OA\Property(property: "prezime_partnera", type: "string", example: "Anić"),
                    new OA\Property(property: "datum_rodjenja_partnera", type: "string", format: "date", example: "1995-05-10"),
                    new OA\Property(property: "partner_pol", type: "string", enum: ["M", "Z"], example: "Z"),
                    new OA\Property(property: "broj_licnog_dokumenta_partnera", type: "string", example: "987654321"),
                ],
                type: "object"
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Zahtev uspešno ažuriran"),
            new OA\Response(response: 404, description: "Zahtev nije pronađen"),
            new OA\Response(response: 422, description: "Validaciona greška")
        ]
    )]
    //FUNKCIJA KOJA ZA UPDATE ZAHTEVA
    public function update(Request $request, $id)
    {
        $zahtev = Zahtev::find($id);
        if (!$zahtev) {
            return response()->json(['message' => 'Zahtev nije pronadjen.'], 404);
        }

        // koji tip ce biti nakon update-a
        $finalTipZahteva = $request->input('tip_zahteva', $zahtev->tip_zahteva);

        //  mora biti najkasnije juče
        $maxDatumPromene = now()->subDay()->toDateString();

        // Validacija glavnih polja
        $validator = Validator::make($request->all(), [
            'tip_zahteva' => 'sometimes|in:prebivaliste,bracni_status',
            'status' => 'sometimes|string|max:255',
            'datum_kreiranja' => 'sometimes|date',
            'korisnik_id' => 'sometimes|integer|exists:users,id',

            'broj_licnog_dokumenta' => 'sometimes|string|max:255',

            
            'datum_promene' => [
                'sometimes',
                'date',
                'before_or_equal:' . $maxDatumPromene,
            ],

            // bracni_status 
            'tip_promene' => $finalTipZahteva === 'bracni_status'
                ? 'required|in:razvod,sklapanje_braka'
                : 'sometimes|nullable|in:razvod,sklapanje_braka',

            'ime_partnera' => $finalTipZahteva === 'bracni_status'
                ? 'required|string|max:255'
                : 'sometimes|nullable|string|max:255',

            'prezime_partnera' => $finalTipZahteva === 'bracni_status'
                ? 'required|string|max:255'
                : 'sometimes|nullable|string|max:255',

            'datum_rodjenja_partnera' => $finalTipZahteva === 'bracni_status'
                ? ['required', 'date', 'before_or_equal:' . now()->subYears(18)->toDateString()]
                : 'sometimes|nullable|date',

            'partner_pol' => $finalTipZahteva === 'bracni_status'
                ? 'required|in:M,Z'
                : 'sometimes|nullable|in:M,Z',

            'broj_licnog_dokumenta_partnera' => $finalTipZahteva === 'bracni_status'
                ? 'required|string|max:255'
                : 'sometimes|nullable|string|max:255',
        ], [
            'datum_promene.before_or_equal' =>
                'Datum promene mora biti najmanje 1 dan pre današnjeg datuma.',
            'datum_rodjenja_partnera.before_or_equal' =>
                'Partner mora imati najmanje 18 godina.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validacija nije prosla.',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();
        $zahtev->update($data);

        // Ažuriranje  adresa
        if ($request->has('stara_adresa')) {
            $zahtev->staraAdresa()->update($request->input('stara_adresa'));
        }
        if ($request->has('nova_adresa')) {
            $zahtev->novaAdresa()->update($request->input('nova_adresa'));
        }

        return response()->json(new ZahtevResource($zahtev), 200);
    }

    #[OA\Delete(
        path: "/api/zahtev/{id}",
        summary: "Brisanje zahteva",
        tags: ["Zahtev"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID zahteva",
                schema: new OA\Schema(type: "integer"),
                example: 1
            )
        ],
        responses: [
            new OA\Response(response: 200, description: "Zahtev uspešno obrisan"),
            new OA\Response(response: 404, description: "Zahtev nije pronađen")
        ]
    )]
    //FUNKCIJA KOJA BRISE ZAHTEV
    public function destroy($id)
    {
        $zahtev = Zahtev::find($id);

        if (!$zahtev) {
            return response()->json(['message' => 'Zahtev nije pronadjen.'], 404);
        }

        $zahtev->delete();
        return response()->json(['message' => 'Zahtev je obrisan.'], 200);
    }

    

    #[OA\Get(
        path: "/api/admin/statistikaZahteva",
        summary: "Statistika svih zahteva (čekajući, odobreni, odbijeni) - samo admin",
        tags: ["Admin"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(response: 200, description: "Statistika zahteva"),
            new OA\Response(response: 401, description: "Unauthorized"),
            new OA\Response(response: 403, description: "Forbidden - nije admin")
        ]
    )]
    //FUNKCIJA KOJA VRACA STATISTIKU SVIH ZAHTEVA, SVIH KORISNIKA
    public function statistikaZahteva()
    {
        $totalCekajuci = Zahtev::where('status', 'kreiran')->count();
        $totalOdobreni = Zahtev::where('status', 'odobren')->count();
        $totalOdbijeni = Zahtev::where('status', 'odbijen')->count();

        return response()->json([
            'cekajuci' => $totalCekajuci,
            'odobreni' => $totalOdobreni,
            'odbijeni' => $totalOdbijeni,
        ]);
    }

    #[OA\Get(
        path: "/api/admin/neobradjeniZahtevi",
        summary: "Lista neobrađenih zahteva - samo admin",
        tags: ["Admin"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "tip_zahteva", in: "query", required: false, description: "Filter po tipu zahteva", schema: new OA\Schema(type: "string", enum: ["prebivaliste", "bracni_status"])),
        ],
        responses: [
            new OA\Response(response: 200, description: "Lista neobrađenih zahteva"),
            new OA\Response(response: 401, description: "Unauthorized"),
            new OA\Response(response: 403, description: "Forbidden - nije admin")
        ]
    )]
    //FUNKCIJA KOJA VRACA SVE ZAHTEVE KOJI NISU OBRADJENI ( GET /api/admin/neobradjeniZahtevi)
    public function neobradjeniZahtevi(Request $request)
    {
        $query = Zahtev::where('status', 'kreiran')
            ->with('korisnik:id,ime,prezime')
            ->select('id', 'tip_zahteva', 'korisnik_id', 'status', 'datum_kreiranja');

        // Filter po tipu zahteva (prebivaliste / bracni_status)
        if ($request->filled('tip_zahteva')) {
            $query->where('tip_zahteva', $request->get('tip_zahteva'));
        }

        $zahtevi = $query->orderBy('datum_kreiranja', 'desc')->get();

        return response()->json($zahtevi);
    }

    #[OA\Post(
        path: "/api/admin/zahtevi/{id}/odobri",
        summary: "Odobravanje zahteva - samo admin",
        tags: ["Admin"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID zahteva",
                schema: new OA\Schema(type: "integer"),
                example: 1
            )
        ],
        responses: [
            new OA\Response(response: 200, description: "Zahtev odobren"),
            new OA\Response(response: 401, description: "Unauthorized"),
            new OA\Response(response: 403, description: "Forbidden - nije admin"),
            new OA\Response(response: 404, description: "Zahtev nije pronađen")
        ]
    )]
    // FUNKCIJA KOJA ODOBRAVA ZAHTEV POST /api/admin/zahtevi/{id}/odobri
    public function odobriZahtev($id)
    {
        $zahtev = Zahtev::findOrFail($id);
        $zahtev->status = 'odobren';
        $zahtev->save();

        return response()->json([
            'message' => 'Zahtev je odobren.',
            'zahtev' => $zahtev
        ]);
    }

    #[OA\Post(
        path: "/api/admin/zahtevi/{id}/odbij",
        summary: "Odbijanje zahteva - samo admin",
        tags: ["Admin"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID zahteva",
                schema: new OA\Schema(type: "integer"),
                example: 1
            )
        ],
        responses: [
            new OA\Response(response: 200, description: "Zahtev odbijen"),
            new OA\Response(response: 401, description: "Unauthorized"),
            new OA\Response(response: 403, description: "Forbidden - nije admin"),
            new OA\Response(response: 404, description: "Zahtev nije pronađen")
        ]
    )]
    // FUNKCIJA KOJA ODBIJA ZAHTEV POST /api/admin/zahtevi/{id}/odbij
    public function odbijZahtev($id)
    {
        $zahtev = Zahtev::findOrFail($id);
        $zahtev->status = 'odbijen';
        $zahtev->save();

        return response()->json([
            'message' => 'Zahtev je odbijen.',
            'zahtev' => $zahtev
        ]);
    }
}