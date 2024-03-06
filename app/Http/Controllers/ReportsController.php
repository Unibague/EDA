<?php

namespace App\Http\Controllers;

use App\Models\AssessmentPeriod;
use App\Models\FormAnswer;
use App\Models\FormAnswers;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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



    public function getAssessmentPDF(Request $request){
        $assessmentPeriodId = $request->input('assessmentPeriodId');
        if($assessmentPeriodId == null){
            $assessmentPeriodId = AssessmentPeriod::getActiveAssessmentPeriod()->id;
        }

        $user = auth()->user();


        if($user->role()['name'] === "administrador"){
            $assessmentPeriodName = AssessmentPeriod::select(['name'])->where('id', '=',  $assessmentPeriodId)->first()->name;
            $labels = json_decode($request->input('labels'));
            $functionaryUserId = $request->input('functionaryUserId');
            $functionaryName = $request->input('functionaryName');
            $graph = $request->input('graph');
            $grades = json_decode($request->input('grades'));
            $openAnswers = FormAnswer::getFunctionaryOpenAnswers($functionaryUserId, $assessmentPeriodId);
            return view('assessmentReport', compact( 'assessmentPeriodName','labels','functionaryName','grades','graph', 'openAnswers'));
        }

/*        $userId = $user['id'];
        $labels = DB::table('competences')->where('assessment_period_id', '=', $activeAssessmentPeriodId)
            ->where('position','>',$deletedCompetencePosition)->orderBy('position', 'DESC')->get();
        dd($labels)*/


    }

    public function getCommitmentsStatusPDF(Request $request){

        $activeAssessmentPeriod = AssessmentPeriod::getActiveAssessmentPeriod();
        $assessmentPeriodName = $activeAssessmentPeriod->name;
        $user = auth()->user();
        $functionaryName = $user->name;

        $commitments = DB::table('commitments as c')->select(['t.name as Tipo de compromiso', 'c.due_date as Fecha máxima'])
            ->where('assessment_period_id', '=', $activeAssessmentPeriod->id)
            ->where('user_id','=', $user->id)->orderBy('due_date', 'ASC')
            ->where('c.done', '=', '0')
            ->join('users as u', 'c.user_id', '=', 'u.id')
            ->join('trainings as t', 'c.training_id', '=','t.id')->get();

        return view('commitmentReport', compact( 'assessmentPeriodName','functionaryName', 'commitments'));

        /*        $userId = $user['id'];
                $labels = DB::table('competences')->where('assessment_period_id', '=', $activeAssessmentPeriodId)
                    ->where('position','>',$deletedCompetencePosition)->orderBy('position', 'DESC')->get();
                dd($labels)*/


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
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
