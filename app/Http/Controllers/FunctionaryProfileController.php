<?php

namespace App\Http\Controllers;

use App\Helpers\AtlanteProvider;
use App\Models\AssessmentPeriod;
use App\Models\Dependency;
use App\Models\FunctionaryProfile;
use App\Models\Position;
use App\Models\TeacherProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Mockery\Undefined;
use SebastianBergmann\LinesOfCode\RuntimeException;

class FunctionaryProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request): JsonResponse
    {
        $activeAssessmentPeriodId = AssessmentPeriod::getActiveAssessmentPeriod()->id;
        $dependency = $request->input('dependency');
        $functionaryProfile = $request->input('functionaryProfile');

        if ($dependency !== null){
            return response()->json(FunctionaryProfile::getFunctionariesAssessments($dependency['identifier']));
        }

        if ($functionaryProfile !== null){
            return response()->json(FunctionaryProfile::where('assessment_period_id', '=', $activeAssessmentPeriodId)->where('user_id', '!=', $functionaryProfile['user_id'])
                ->with('user')->get()->sortBy('user.name')->values()->all());
        }

        return response()->json(FunctionaryProfile::where('assessment_period_id', '=', $activeAssessmentPeriodId)
            ->with('user')->get()->sortBy('user.name')->values()->all());

    }

    public function sync(): JsonResponse
    {
        try {
            $functionaries = AtlanteProvider::get('functionaries', [
                'type_employee' => 'ADM',
            ], true);

            $finalFunctionaries = [];

            foreach ($functionaries as $functionary){
                //Traerse a los funcionarios que no tengan position = "PROFESOR" || position = "DOCENTE TIEMPO COMPLETO"

                if($functionary['position'] !== "PROFESOR" && $functionary['position'] !== "DOCENTE TIEMPO COMPLETO" && $functionary['email'] !== "" && $functionary['faculty'] !== ""){
                    $finalFunctionaries [] = $functionary;
                }
            }

        } catch (\JsonException $e) {
            return response()->json(['message' => 'Ha ocurrido un error con la fuente de datos: ' . $e->getMessage()], 400);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => 'Ha ocurrido el siguiente error: ' . $e->getMessage()], 400);
        }
        try {
            FunctionaryProfile::createOrUpdateFromArray($finalFunctionaries);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
        return response()->json(['message' => 'Los docentes se han sincronizado exitosamente']);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
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
    public function edit(Dependency $dependency, FunctionaryProfile $functionaryProfile)
    {
        return Inertia::render('Functionaries/FunctionaryAssignment', ['functionary' => $functionaryProfile, 'dependency' => $dependency]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {

    }
}
