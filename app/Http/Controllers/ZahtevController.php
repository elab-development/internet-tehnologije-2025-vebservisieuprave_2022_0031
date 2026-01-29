<?php


namespace App\Http\Controllers;


use Illuminate\Support\Facades\Validator;
use App\Models\Zahtev;
use Illuminate\Http\Request;
use App\Http\Resources\ZahtevResource;
use Illuminate\Support\Facades\Log;

class ZahtevController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    // koristimo sa GET, da vrati sve zahteve
    public function index()
    {
        return ZahtevResource::collection(Zahtev::all());
        
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }
        //get/zahtevi/moji
        // da prikaze izlistane zahteve ulogovanog korisnika
     public function moje(Request $request){
            $userId=$request->user()->id;
            Log::info('ID ulogovanog korisnika: ' . $userId);

            $zahtevi=Zahtev::where('korisnik_id', $userId)
            ->orderByDesc('datum_kreiranja')
            ->get();

            Log::info('Broj zahteva: ' . $zahtevi->count());
                 return ZahtevResource::collection($zahtevi);
        }
        //get/zahtevi/moji ali samo za bracni status
        // da prikaze izlistane zahteve za br status ulogovanog korisnika
        public function mojiBracniStatus(Request $request){
    $userId = $request->user()->id;
    Log::info('ID ulogovanog korisnika: ' . $userId);

    $zahtevi = Zahtev::where('korisnik_id', $userId)
                ->where('tip_zahteva', Zahtev::BRACNI_STATUS) // koristi const iz modela
                ->orderByDesc('datum_kreiranja')
                ->get();

    Log::info('Broj zahteva: ' . $zahtevi->count());

    return ZahtevResource::collection($zahtevi);
}

    /**
     * Store a newly created resource in storage.
     */
    //Sa POST zahtevom , on sluzi za kreiranje objekta
    public function store(Request $request)
    {
       $validator = Validator::make($request->all(), [
        'tip_zahteva' => 'required|in:prebivaliste,bracni_status',
        'status' => 'required|string|max:255',
        'datum_kreiranja' => 'nullable|date',

        'korisnik_id' => 'required|integer|exists:users,id',

        'tip_promene' => 'nullable|in:razvod,sklapanje_braka',
        'ime_partnera' => 'nullable|string|max:255',
        'prezime_partnera' => 'nullable|string|max:255',
        'datum_rodjenja_partnera' => 'nullable|date',
        'partner_pol' => 'nullable|in:M,Z',
        'broj_licnog_dokumenta' => 'nullable|string|max:255',
        'broj_licnog_dokumenta_partnera' => 'nullable|string|max:255',
        'datum_promene' => 'nullable|date',
    ]);
    if ($validator->fails()) {
        return response()->json([
            'message'=> 'Validacija nije prosla.',
            'errors' => $validator->errors()], 422); 
    }
        $data=$validator-> validated();
        $zahtev=Zahtev::create($data);
        return response()-> json(new ZahtevResource($zahtev, 201));
    }

    /**
     * Display the specified resource.
     */
    //sa get zahtevom, da vratimo jedan zahtev
    //get/zahtev/(zahtevid)
    public function show( $zahtev_id)
    {
        return new ZahtevResource(Zahtev::findOrFail($zahtev_id));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Zahtev $zahtev)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    //putm azurira objekta
    public function update(Request $request,  $id)
    {  $zahtev=Zahtev::find($id);
         if(!$zahtev){
        return response()->json(['message'=> 'Zahtev nije pronadjen.'], 404);
        }
        
        $validator = Validator::make($request->all(), [
        'tip_zahteva' => 'sometimes|in:prebivaliste,bracni_status',
        'status' => 'sometimes|string|max:255',
        'datum_kreiranja' => 'sometimes|date',

        'korisnik_id' => 'sometimes|integer|exists:users,id',

        'tip_promene' => 'sometimes|in:razvod,sklapanje_braka',
        'ime_partnera' => 'sometimes|string|max:255',
        'prezime_partnera' => 'sometimes|string|max:255',
        'datum_rodjenja_partnera' => 'sometimes|date',
        'partner_pol' => 'sometimes|in:M,Z',
        'broj_licnog_dokumenta' => 'sometimes|string|max:255',
        'broj_licnog_dokumenta_partnera' => 'sometimes|string|max:255',
        'datum_promene' => 'sometimes|date',
    ]);
    if ($validator->fails()) {
        return response()->json([
            'message'=> 'Validacija nije prosla.',
            'errors' => $validator->errors()], 422); 
    }
    $data=$validator-> validated();
        $zahtev->update($data);
        return response()-> json(new ZahtevResource($zahtev, 200));
    }

    /**
     * Remove the specified resource from storage.
     */
    //delete
    public function destroy( $id)
    {
        $zahtev=Zahtev::find($id);

        if(!$zahtev){
        return response()->json(['message'=> 'Zahtev nije pronadjen.'], 404);

        }
        $zahtev->delete();
        return response()->json(['message'=> 'Zahtev je obrisan.'], 200);

    }

    


}
