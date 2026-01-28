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
    public function store(Request $request)
    {
       $validator = Validator::make($request->all(), [
        'tip_dokumenta' => 'required|string|in:licna_karta,pasos',
            'lokacija' => 'required|string|max:255',
            'datum_vreme' => 'required|date',
            'korisnik_id' => 'required|exists:users,id', // proverava da li korisnik postoji
    ]);

    if ($validator->fails()) {
        return response()->json([
            'message'=> 'Validacija nije prosla.',
            'errors' => $validator->errors()], 422); 
    }
        $data=$validator-> validated();
        $termin=Termin::create($data);
        return response()-> json(new TerminResource($termin, 201));
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
       return new TerminResource(Termin::findOrFail($id));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Termin $termin)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
       $termin=Termin::find($id);
         if(!$termin){
        return response()->json(['message'=> 'Zahtev nije pronadjen.'], 404);
        }
        
        $validator = Validator::make($request->all(), [
        'tip_dokumenta' => 'required|string|in:licna_karta,pasos',
            'lokacija' => 'required|string|max:255',
            'datum_vreme' => 'required|date',
            'korisnik_id' => 'required|exists:users,id', // proverava da li korisnik postoji
    ]);
    if ($validator->fails()) {
        return response()->json([
            'message'=> 'Validacija nije prosla.',
            'errors' => $validator->errors()], 422); 
    }
    $data=$validator-> validated();
        $termin->update($data);
        return response()-> json(new TerminResource($termin, 200));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy( $id)
    {
        $termin=Termin::find($id);

        if(!$termin){
        return response()->json(['message'=> 'Zahtev nije pronadjen.'], 404);

        }
        $termin->delete();
        return response()->json(['message'=> 'Zahtev je obrisan.'], 200);
    }
}
