<?php

namespace App\Http\Controllers;

use App\Models\AssessmentPeriod;
use App\Models\Competence;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CompetenceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->json(Competence::all());
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        Competence::createCompetence($request->all());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        Competence::createCompetence($request->all());
        $competence = Competence::whereName($request->input('name'))->first();
        return response()->json(['message' => 'Competencia creada exitosamente']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Competence $competence)
    {
        $competence->update($request->all());
        return response()->json(['message' => 'Competencia actualizada exitosamente']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Competence $competence)
    {
        try {
            $competence->delete();
        } catch (QueryException $e) {
            if ($e->getCode() === "23000") {
                return response()->json(['message' => 'No puedes eliminar una posición si ya está asociado a algún funcionario'], 400);
            }
        }
        return response()->json(['message' => 'Posición eliminada exitosamente']);
    }
}
