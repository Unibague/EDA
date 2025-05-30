<?php

namespace App\Http\Controllers;

use App\Helpers\AtlanteProvider;
use App\Models\AssessmentPeriod;
use App\Models\Dependency;
use App\Models\FunctionaryProfile;
use App\Models\Position;
use App\Models\Role;
use App\Models\TeacherProfile;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Js;
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

        $assessmentPeriodId = $request->input('assessmentPeriodId');
        $report = $request->input('report');
        if ($assessmentPeriodId === null){
            $assessmentPeriodId = AssessmentPeriod::getActiveAssessmentPeriod()->id;
        }

        $functionaryProfile = $request->input('functionaryProfile');

        if ($functionaryProfile !== null){
            return response()->json(FunctionaryProfile::where('assessment_period_id', '=', $assessmentPeriodId)->where('user_id', '!=', $functionaryProfile['user_id'])
                ->with('user')->get()->sortBy('user.name')->values()->all());
        }

        return response()->json(FunctionaryProfile::where('assessment_period_id', '=', $assessmentPeriodId)
            ->with(['user', 'user.commitments' => function($query) use ($assessmentPeriodId) {
                $query->where('assessment_period_id', $assessmentPeriodId);
            }])->get()->sortBy('user.name')->values()->all());

    }

    public function sync(): JsonResponse
    {
        try {
            $functionaries = AtlanteProvider::get('functionaries', [
                'type_employee' => 'ADM',
            ], true);

        FunctionaryProfile::createOrUpdateFromArray($functionaries);

        } catch (\JsonException $e) {
            return response()->json(['message' => 'Ha ocurrido un error con la fuente de datos: ' . $e->getMessage()], 400);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => 'Ha ocurrido el siguiente error: ' . $e->getMessage()], 400);
        }
        return response()->json(['message' => 'Los funcionarios se han sincronizado exitosamente']);
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

    public function changeStatus(Request $request, FunctionaryProfile $functionaryProfile): JsonResponse
    {
        $status = $request->input('status');
        $functionaryProfile->is_active = $status;
        $functionaryProfile->save();
        if ($status === 0) {
            return response()->json(['message' => 'El funcionario ha sido suspendido. Este dejará de sincronizarse y no podrá participar de la evaluación']);
        }
        return response()->json(['message' => "El estado del funcionario ha sido cambiado a Activo."]);
    }


    public function getPendingChanges()
    {
        $activeAssessmentPeriodId = AssessmentPeriod::getActiveAssessmentPeriod()->id;
        $pendingChanges = DB::table('functionaries_data_changes as fdc')->select(['fdc.id','fdc.user_id', 'fdc.payload', 'u.name'])
            ->join('users as u', 'u.id', '=', 'fdc.user_id')
        ->where('assessment_period_id', '=', $activeAssessmentPeriodId)->get();

        foreach ($pendingChanges as $user){
            $user->official_answers = DB::table('functionary_profiles as fp')
                ->where('fp.assessment_period_id', '=', $activeAssessmentPeriodId)
                ->where('fp.user_id', '=', $user->user_id)->first();

            $user->assignments = DB::table('assessments as a')->select(['u.name', 'a.role', 'a.pending', 'd.name as dependency_name'])
                ->join('users as u', 'u.id', '=', 'a.evaluated_id')
                ->join('dependencies as d','d.identifier','=','a.dependency_identifier')
                ->where('a.role', '!=','autoevaluación')
                ->where('a.evaluator_id', '=', $user->user_id)
                ->where('a.assessment_period_id', '=', $activeAssessmentPeriodId)->orderBy('a.role', 'ASC')->get();
        }

        return response()->json($pendingChanges);
    }


    public function approveChange(int $userId){

        $activeAssessmentPeriodId = AssessmentPeriod::getActiveAssessmentPeriod()->id;

        try{
            //As the change is approved, then we proceed
            $change = DB::table('functionaries_data_changes')->where('assessment_period_id', '=', $activeAssessmentPeriodId)
                ->where('user_id','=', $userId)->first();
            $payload = json_decode($change->payload, false);

            //If the payload is null, then we have to remove that users functionary_profile
            if($payload->user_id === null){
                if (self::userHasAssignments($userId)){
                    return response()->json(['message' => 'Debes eliminar las asignaciones activas de este usuario antes de eliminarlo del periodo de evaluación actual'], 400);
                }
                DB::table('functionary_profiles')->where("assessment_period_id", '=', $activeAssessmentPeriodId)
                ->where('user_id','=', $userId)->delete();
            }

            //If that's not the case, then we just have to do the upsert of the data on the functionary profiles info.
            else{
                $functionaryCurrentInfo = DB::table('functionary_profiles')->where('user_id','=',$payload->user_id)
                    ->where("assessment_period_id", '=', $activeAssessmentPeriodId)->first();

                if($payload->dependency_identifier !== $functionaryCurrentInfo->dependency_identifier){

                    $functionaryRoleId= Role::getRoleIdByName('funcionario');
                    //Then it means that the user changed dependency, so we have to perform that change
                    DB::table('dependency_user')->where('user_id','=',$payload->user_id)
                        ->where('role_id','=',$functionaryRoleId)
                        ->update(['is_active' => 0]);

                    DB::table('dependency_user')->updateOrInsert(['user_id' => $payload->user_id,
                        'dependency_identifier' => $payload->dependency_identifier],['role_id' => $functionaryRoleId, 'is_active' => true]);
                }
                DB::table('functionary_profiles')->updateOrInsert(["user_id" => $payload->user_id, "assessment_period_id" => $activeAssessmentPeriodId],
                    ["name" => $payload->name,  "identification_number" => $payload->identification_number, "dependency_name" => $payload->dependency_name,
                        "dependency_identifier" => $payload->dependency_identifier,"job_title" => $payload->job_title,
                        "hire_date" => $payload->hire_date]);
            }

            //If the upsert is done correctly, then we proceed to delete the change on functionaries_data_changes
            DB::table('functionaries_data_changes')->where('assessment_period_id', '=', $activeAssessmentPeriodId)
                ->where('user_id','=', $userId)->delete();

        } catch (QueryException $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }

        return response()->json(['message' => 'Cambio aceptado correctamente']);

    }


    public function declineChange(int $userId){

        $activeAssessmentPeriodId = AssessmentPeriod::getActiveAssessmentPeriod()->id;
        try{
            //As the change is denied, then we proceed to simply delete the change
            DB::table('functionaries_data_changes')->where('assessment_period_id', '=', $activeAssessmentPeriodId)
                ->where('user_id','=', $userId)->delete();

        } catch (QueryException $e) {
            return response()->json(['message' => 'No se pudo rechazar el cambio, intenta de nuevo más tarde.'], 400);
        }

        return response()->json(['message' => 'Cambio reachazado correctamente']);

    }

    public function userHasAssignments(int $userId){

        $activeAssessmentPeriodId = AssessmentPeriod::getActiveAssessmentPeriod()->id;

        $assignments = DB::table('assessments as a')->where('a.evaluator_id','=',$userId)
            ->where('a.role', '!=', 'autoevaluación')->where('pending','=', 1)
            ->where('a.assessment_period_id','=', $activeAssessmentPeriodId)->first();

        if($assignments){
            return true;
        }
        return false;
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
