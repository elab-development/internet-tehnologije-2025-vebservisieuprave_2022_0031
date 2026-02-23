<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use App\Models\Zahtev;
use App\Models\Adresa;
use Illuminate\Http\Request;
use App\Http\Resources\ZahtevResource;
use Illuminate\Support\Facades\Log;

class ZahtevController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return ZahtevResource::collection(Zahtev::all());
    }

    public function create()
    {
        //
    }

    //get/zahtevi/moji
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

    //get/zahtevi/moji ali samo za bracni status
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

    //get/zahtevi/moji ali samo za prebivaliste
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

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // ✅ datum_promene mora biti najkasnije juče (minimum 1 dan u prošlosti)
        $maxDatumPromene = now()->subDay()->toDateString();

        $validator = Validator::make($request->all(), [
            'tip_zahteva' => 'required|in:prebivaliste,bracni_status',

            // zajednicka polja (na frontu su obavezna)
            'broj_licnog_dokumenta' => 'required|string|max:255',

            // ✅ GLAVNA IZMENA
            'datum_promene' => [
                'required',
                'date',
                'before_or_equal:' . $maxDatumPromene,
            ],

            // bracni_status polja - obavezna samo ako je tip_zahteva bracni_status
            'tip_promene' => 'required_if:tip_zahteva,bracni_status|in:razvod,sklapanje_braka',
            'ime_partnera' => 'required_if:tip_zahteva,bracni_status|string|max:255',
            'prezime_partnera' => 'required_if:tip_zahteva,bracni_status|string|max:255',

            // ✅ partner mora biti 18+ (samim tim nije ni buducnost)
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

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $zahtev = Zahtev::find($id);
        if (!$zahtev) {
            return response()->json(['message' => 'Zahtev nije pronadjen.'], 404);
        }

        // koji tip ce biti nakon update-a
        $finalTipZahteva = $request->input('tip_zahteva', $zahtev->tip_zahteva);

        // ✅ datum_promene mora biti najkasnije juče (minimum 1 dan u prošlosti)
        $maxDatumPromene = now()->subDay()->toDateString();

        // Validacija glavnih polja
        $validator = Validator::make($request->all(), [
            'tip_zahteva' => 'sometimes|in:prebivaliste,bracni_status',
            'status' => 'sometimes|string|max:255',
            'datum_kreiranja' => 'sometimes|date',
            'korisnik_id' => 'sometimes|integer|exists:users,id',

            'broj_licnog_dokumenta' => 'sometimes|string|max:255',

            // ✅ GLAVNA IZMENA
            'datum_promene' => [
                'sometimes',
                'date',
                'before_or_equal:' . $maxDatumPromene,
            ],

            // bracni_status polja (obavezna ako je finalni tip bracni_status)
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

        // ✅ Ažuriranje povezanih adresa
        if ($request->has('stara_adresa')) {
            $zahtev->staraAdresa()->update($request->input('stara_adresa'));
        }
        if ($request->has('nova_adresa')) {
            $zahtev->novaAdresa()->update($request->input('nova_adresa'));
        }

        return response()->json(new ZahtevResource($zahtev), 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $zahtev = Zahtev::find($id);

        if (!$zahtev) {
            return response()->json(['message' => 'Zahtev nije pronadjen.'], 404);
        }

        $zahtev->delete();
        return response()->json(['message' => 'Zahtev je obrisan.'], 200);
    }

    public function exportCsv(Request $request)
    {
        $userId = $request->user()->id;

        $zahtevi = Zahtev::with(['staraAdresa', 'novaAdresa', 'korisnik.termin'])
            ->where('korisnik_id', $userId)
            ->orderBy('datum_kreiranja', 'asc')
            ->get();

        $columns = ['id', 'tip_zahteva', 'status', 'datum_kreiranja', 'stara_adresa', 'nova_adresa', 'termin'];

        $callback = function () use ($zahtevi, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns, ';');

            foreach ($zahtevi as $z) {
                fputcsv($file, [
                    $z->id,
                    $z->tip_zahteva,
                    $z->status,
                    $z->datum_kreiranja ? $z->datum_kreiranja->format('Y-m-d') : null,
                    optional($z->staraAdresa)->ulica,
                    optional($z->novaAdresa)->ulica,
                    optional($z->korisnik->termin)->lokacija
                ], ';');
            }

            fclose($file);
        };

        $fileName = 'zahtevi_' . $userId . '_' . now()->format('Ymd_His') . '.csv';

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }
}




