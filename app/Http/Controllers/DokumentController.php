<?php

namespace App\Http\Controllers;

use App\Models\Dokument;
use Illuminate\Http\Request;
use App\Http\Resources\DokumentResource;
use Illuminate\Support\Facades\Validator;


class DokumentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return DokumentResource::collection(Dokument::all());
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
        'nazivFajla' => 'required|string|max:255',
            'putanja' => 'required|string|max:500', // putanja do fajla
            'tipDokumeta' => 'required|string|in:izvod,presuda,licna_karta,pasos',
            'broj_dokumenta' => 'required|string|max:100',
            'organ_izdavanja' => 'required|string|max:255',
            'zahtev_id' => 'required|exists:zahtevi,id', // proverava da li zahtev postoji
    ]);
    if ($validator->fails()) {
        return response()->json([
            'message'=> 'Validacija nije prosla.',
            'errors' => $validator->errors()], 422); 
    }
        $data=$validator-> validated();
        $dokument=Dokument::create($data);
        return response()-> json(new DokumentResource($dokument, 201));
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        return new DokumentResource(Dokument::findOrFail($id));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Dokument $dokument)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request,  $id)
    {
       $dokument=Dokument::find($id);
         if(!$dokument){
        return response()->json(['message'=> 'Zahtev nije pronadjen.'], 404);
        }
        
        $validator = Validator::make($request->all(), [
        'nazivFajla' => 'required|string|max:255',
            'putanja' => 'required|string|max:500', // putanja do fajla
            'tipDokumeta' => 'required|string|in:izvod,presuda,licna_karta,pasos',
            'broj_dokumenta' => 'required|string|max:100',
            'organ_izdavanja' => 'required|string|max:255',
            'zahtev_id' => 'required|exists:zahtevi,id', // proverava da li zahtev postoji
    ]);
    if ($validator->fails()) {
        return response()->json([
            'message'=> 'Validacija nije prosla.',
            'errors' => $validator->errors()], 422); 
    }
    $data=$validator-> validated();
        $dokument->update($data);
        return response()-> json(new DokumentResource($dokument, 200));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
         $dokument=Dokument::find($id);

        if(!$dokument){
        return response()->json(['message'=> 'Zahtev nije pronadjen.'], 404);

        }
        $dokument->delete();
        return response()->json(['message'=> 'Zahtev je obrisan.'], 200);
    }
}
