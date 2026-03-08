<?php

namespace App\Http\Controllers;

use App\Models\Adresa;
use Illuminate\Http\Request;
use App\Http\Resources\AdresaResource;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;


class AdresaController extends Controller
{
    #[OA\Get(
        path: "/api/adresa",
        summary: "Lista svih adresa",
        tags: ["Adresa"],
        responses: [
            new OA\Response(response: 200, description: "Lista adresa")
        ]
    )]
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

    #[OA\Post(
        path: "/api/adresa",
        summary: "Kreiranje nove adrese",
        tags: ["Adresa"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["zahtev_id", "mesto", "opstina", "grad", "postanski_broj", "trajanje_prebivalista", "uloga_adrese"],
                properties: [
                    new OA\Property(property: "zahtev_id", type: "integer", example: 1),
                    new OA\Property(property: "ulica", type: "string", example: "Knez Mihailova"),
                    new OA\Property(property: "broj", type: "integer", example: 5),
                    new OA\Property(property: "mesto", type: "string", example: "Beograd"),
                    new OA\Property(property: "opstina", type: "string", example: "Stari Grad"),
                    new OA\Property(property: "grad", type: "string", example: "Beograd"),
                    new OA\Property(property: "postanski_broj", type: "string", example: "11000"),
                    new OA\Property(property: "trajanje_prebivalista", type: "string", enum: ["stalna", "privremena"], example: "stalna"),
                    new OA\Property(property: "uloga_adrese", type: "string", enum: ["nova", "stara"], example: "nova"),
                ],
                type: "object"
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Adresa uspešno kreirana"),
            new OA\Response(response: 422, description: "Validaciona greška")
        ]
    )]
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

    #[OA\Get(
        path: "/api/adresa/{id}",
        summary: "Prikaz jedne adrese",
        tags: ["Adresa"],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID adrese",
                schema: new OA\Schema(type: "integer"),
                example: 1
            )
        ],
        responses: [
            new OA\Response(response: 200, description: "Podaci o adresi"),
            new OA\Response(response: 404, description: "Adresa nije pronađena")
        ]
    )]
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

    #[OA\Put(
        path: "/api/adresa/{id}",
        summary: "Ažuriranje adrese",
        tags: ["Adresa"],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID adrese",
                schema: new OA\Schema(type: "integer"),
                example: 1
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["zahtev_id", "mesto", "opstina", "grad", "postanski_broj", "trajanje_prebivalista", "uloga_adrese"],
                properties: [
                    new OA\Property(property: "zahtev_id", type: "integer", example: 1),
                    new OA\Property(property: "ulica", type: "string", example: "Knez Mihailova"),
                    new OA\Property(property: "broj", type: "integer", example: 5),
                    new OA\Property(property: "mesto", type: "string", example: "Beograd"),
                    new OA\Property(property: "opstina", type: "string", example: "Stari Grad"),
                    new OA\Property(property: "grad", type: "string", example: "Beograd"),
                    new OA\Property(property: "postanski_broj", type: "string", example: "11000"),
                    new OA\Property(property: "trajanje_prebivalista", type: "string", enum: ["stalna", "privremena"], example: "stalna"),
                    new OA\Property(property: "uloga_adrese", type: "string", enum: ["nova", "stara"], example: "nova"),
                ],
                type: "object"
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Adresa uspešno ažurirana"),
            new OA\Response(response: 404, description: "Adresa nije pronađena"),
            new OA\Response(response: 422, description: "Validaciona greška")
        ]
    )]
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

    #[OA\Delete(
        path: "/api/adresa/{id}",
        summary: "Brisanje adrese",
        tags: ["Adresa"],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID adrese",
                schema: new OA\Schema(type: "integer"),
                example: 1
            )
        ],
        responses: [
            new OA\Response(response: 200, description: "Adresa uspešno obrisana"),
            new OA\Response(response: 404, description: "Adresa nije pronađena")
        ]
    )]
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