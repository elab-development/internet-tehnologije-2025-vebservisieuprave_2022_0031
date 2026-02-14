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
    public function index()
    {
        return ZahtevResource::collection(Zahtev::all());
    }

    public function create()
    {
        //
    }

    public function mojiZahteviPaginatedFiltered(Request $request)
    {
        $userId = $request->user()->id;
        $perPage = $request->get('per_page', 10);

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
        return ZahtevResource::collection($query->paginate($perPage));
    }

    /**
     * STORE
     */
    public function store(Request $request)
    {
        // datum_promene mora biti najkasnije juče
        $maxDatumPromene = now()->subDay()->toDateString();

        $validator = Validator::make($request->all(), [
            'tip_zahteva' => 'required|in:prebivaliste,bracni_status',
            'broj_licnog_dokumenta' => 'required|string|max:255',

            // GLAVNA IZMENA
            'datum_promene' => [
                'required',
                'date',
                'before_or_equal:' . $maxDatumPromene,
            ],

            'tip_promene' => 'required_if:tip_zahteva,bracni_status|in:razvod,sklapanje_braka',
            'ime_partnera' => 'required_if:tip_zahteva,bracni_status|string|max:255',
            'prezime_partnera' => 'required_if:tip_zahteva,bracni_status|string|max:255',

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
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validacija nije prošla.',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();
        $data['korisnik_id'] = $request->user()->id;
        $data['status'] = 'kreiran';
        $data['datum_kreiranja'] = now();

        $zahtev = Zahtev::create($data);

        return response()->json(new ZahtevResource($zahtev), 201);
    }

    /**
     * UPDATE
     */
    public function update(Request $request, $id)
    {
        $zahtev = Zahtev::find($id);

        if (!$zahtev) {
            return response()->json(['message' => 'Zahtev nije pronadjen.'], 404);
        }

        $maxDatumPromene = now()->subDay()->toDateString();
        $finalTipZahteva = $request->input('tip_zahteva', $zahtev->tip_zahteva);

        $validator = Validator::make($request->all(), [
            'tip_zahteva' => 'sometimes|in:prebivaliste,bracni_status',
            'broj_licnog_dokumenta' => 'sometimes|string|max:255',

            //GLAVNA IZMENA
            'datum_promene' => [
                'sometimes',
                'date',
                'before_or_equal:' . $maxDatumPromene,
            ],

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

        $zahtev->update($validator->validated());

        return response()->json(new ZahtevResource($zahtev), 200);
    }
}


