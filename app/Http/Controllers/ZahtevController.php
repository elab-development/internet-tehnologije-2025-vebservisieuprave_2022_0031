<?php


namespace App\Http\Controllers;
use Illuminate\Support\Facades\Validator;
use App\Models\Zahtev;
use Illuminate\Http\Request;


class ZahtevController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    // get, da vrati sve zahteve
    public function index()
    {
        return Zahtev::all();
        
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    //post kreiraj zahtev
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
        return response()-> json($zahtev, 201);
    }

    /**
     * Display the specified resource.
     */
    //get, da vratimo jedan zahtev
    //get/zahtev/(zahtevid)
    public function show( $zahtev_id)
    {
        return Zahtev::find($zahtev_id);
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
    public function update(Request $request, Zahtev $zahtev)
    {
        //
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
