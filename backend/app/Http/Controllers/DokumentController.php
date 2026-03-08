<?php

namespace App\Http\Controllers;

use App\Models\Dokument;
use Illuminate\Http\Request;
use App\Http\Resources\DokumentResource;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;


class DokumentController extends Controller
{
    #[OA\Get(
        path: "/api/dokument",
        summary: "Lista svih dokumenata",
        tags: ["Dokument"],
        responses: [
            new OA\Response(response: 200, description: "Lista dokumenata")
        ]
    )]
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

    #[OA\Post(
        path: "/api/dokument",
        summary: "Kreiranje novog dokumenta",
        tags: ["Dokument"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["nazivFajla", "putanja", "tipDokumeta", "broj_dokumenta", "organ_izdavanja", "zahtev_id"],
                properties: [
                    new OA\Property(property: "nazivFajla", type: "string", example: "izvod.pdf"),
                    new OA\Property(property: "putanja", type: "string", example: "documents/izvod.pdf"),
                    new OA\Property(property: "tipDokumeta", type: "string", enum: ["izvod", "presuda", "licna_karta", "pasos"], example: "izvod"),
                    new OA\Property(property: "broj_dokumenta", type: "string", example: "123456"),
                    new OA\Property(property: "organ_izdavanja", type: "string", example: "MUP Srbije"),
                    new OA\Property(property: "zahtev_id", type: "integer", example: 1),
                ],
                type: "object"
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Dokument uspešno kreiran"),
            new OA\Response(response: 422, description: "Validaciona greška")
        ]
    )]
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

    #[OA\Get(
        path: "/api/dokument/{id}",
        summary: "Prikaz jednog dokumenta",
        tags: ["Dokument"],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID dokumenta",
                schema: new OA\Schema(type: "integer"),
                example: 1
            )
        ],
        responses: [
            new OA\Response(response: 200, description: "Podaci o dokumentu"),
            new OA\Response(response: 404, description: "Dokument nije pronađen")
        ]
    )]
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

    #[OA\Put(
        path: "/api/dokument/{id}",
        summary: "Ažuriranje dokumenta",
        tags: ["Dokument"],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID dokumenta",
                schema: new OA\Schema(type: "integer"),
                example: 1
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["nazivFajla", "putanja", "tipDokumeta", "broj_dokumenta", "organ_izdavanja", "zahtev_id"],
                properties: [
                    new OA\Property(property: "nazivFajla", type: "string", example: "izvod.pdf"),
                    new OA\Property(property: "putanja", type: "string", example: "documents/izvod.pdf"),
                    new OA\Property(property: "tipDokumeta", type: "string", enum: ["izvod", "presuda", "licna_karta", "pasos"], example: "izvod"),
                    new OA\Property(property: "broj_dokumenta", type: "string", example: "123456"),
                    new OA\Property(property: "organ_izdavanja", type: "string", example: "MUP Srbije"),
                    new OA\Property(property: "zahtev_id", type: "integer", example: 1),
                ],
                type: "object"
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Dokument uspešno ažuriran"),
            new OA\Response(response: 404, description: "Dokument nije pronađen"),
            new OA\Response(response: 422, description: "Validaciona greška")
        ]
    )]
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

    #[OA\Delete(
        path: "/api/dokument/{id}",
        summary: "Brisanje dokumenta",
        tags: ["Dokument"],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID dokumenta",
                schema: new OA\Schema(type: "integer"),
                example: 1
            )
        ],
        responses: [
            new OA\Response(response: 200, description: "Dokument uspešno obrisan"),
            new OA\Response(response: 404, description: "Dokument nije pronađen")
        ]
    )]
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