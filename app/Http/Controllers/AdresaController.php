<?php

namespace App\Http\Controllers;

use App\Models\Adresa;
use Illuminate\Http\Request;
use App\Http\Resources\AdresaResource;
use Illuminate\Support\Facades\Validator;


class AdresaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return AdresaResource::collection(Adresa::all());
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
         'zahtev_id' => 'required|exists:zahtevi,id', // proverava da li zahtev postoji
            'ulica' => 'nullable|string|max:255',
            'broj' => 'nullable|integer',
            'mesto' => 'required|string|max:255',
            'opstina' => 'required|string|max:255',
            'grad' => 'required|string|max:255',
            'postanski_broj' => 'required|string|max:10',
            'trajanje_prebivalista' => 'required|in:stalna,privremena',
            'uloga_adrese' => 'required|in:nova,stara',
    ]);
    if ($validator->fails()) {
        return response()->json([
            'message'=> 'Validacija nije prosla.',
            'errors' => $validator->errors()], 422); 
    }
        $data=$validator-> validated();
        $adresa=Adresa::create($data);
        return response()-> json(new AdresaResource($adresa, 201));
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        return new AdresaResource(Adresa::findOrFail($id));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Adresa $adresa)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request,  $id)
    {
        $adresa=Adresa::find($id);
         if(!$adresa){
        return response()->json(['message'=> 'Zahtev nije pronadjen.'], 404);
        }
        
        $validator = Validator::make($request->all(), [
        'zahtev_id' => 'required|exists:zahtevi,id', // proverava da li zahtev postoji
            'ulica' => 'nullable|string|max:255',
            'broj' => 'nullable|integer',
            'mesto' => 'required|string|max:255',
            'opstina' => 'required|string|max:255',
            'grad' => 'required|string|max:255',
            'postanski_broj' => 'required|string|max:10',
            'trajanje_prebivalista' => 'required|in:stalna,privremena',
            'uloga_adrese' => 'required|in:nova,stara',
    ]);
    if ($validator->fails()) {
        return response()->json([
            'message'=> 'Validacija nije prosla.',
            'errors' => $validator->errors()], 422); 
    }
    $data=$validator-> validated();
        $adresa->update($data);
        return response()-> json(new AdresaResource($adresa, 200));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy( $id)
    {
        $adresa=Adresa::find($id);

        if(!$adresa){
        return response()->json(['message'=> 'Zahtev nije pronadjen.'], 404);

        }
        $adresa->delete();
        return response()->json(['message'=> 'Zahtev je obrisan.'], 200);
    }
}
