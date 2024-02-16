<?php

namespace App\Http\Controllers;

use App\Models\AssessmentPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FormAnswersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $assessmentPeriodId = $request->input('assessmentPeriodId');

        if($assessmentPeriodId === null){
            $assessmentPeriodId = AssessmentPeriod::getActiveAssessmentPeriod()->id;
        }

        return DB::table('form_answers as fa')
            ->select(['fa.id', 'fa.submitted_at', 'u.name', 'a.role',
                'fa.first_competence_average as c1','fa.second_competence_average as c2','fa.third_competence_average as c3','fa.fourth_competence_average as c4',
                'fa.fifth_competence_average as c5','fa.sixth_competence_average as c6'])
            ->join('forms as f', 'fa.form_id', '=', 'f.id')
            ->join('users as u', 'fa.evaluated_id', '=', 'u.id')
            ->join('assessments as a','fa.id','=','a.form_answer_id')
            ->where('f.creation_assessment_period_id', '=', $assessmentPeriodId)
            ->get();


//        'fa.first_competence_average','fa.second_competence_average','fa.third_competence_average','fa.fourth_competence_average',
//                'fa.fifth_competence_average','fa.sixth_competence_average'

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
