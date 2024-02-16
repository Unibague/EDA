<template>
    <AuthenticatedLayout>
        <Snackbar :timeout="snackbar.timeout" :text="snackbar.text" :type="snackbar.type"
                  :show="snackbar.status" @closeSnackbar="snackbar.status = false"></Snackbar>

        <v-container fluid>
            <div class="d-flex flex-column align-end mb-2" id="malparido">
                <h2 class="align-self-start">Gestionar respuestas de evaluación</h2>
            </div>

            <v-toolbar
                dark
                color="primario"
                class="mb-1"
                height="auto"
            >
                <v-row class="py-3">
                    <v-col cols="3" >
                        <v-autocomplete
                            v-model="assessmentPeriod"
                            flat
                            solo-inverted
                            hide-details
                            :items="assessmentPeriods"
                            :item-text="(assessmentPeriod)=> capitalize(assessmentPeriod.name)"
                            :item-value="(assessmentPeriod)=> (assessmentPeriod.id)"
                            prepend-inner-icon="mdi-home-search"
                            label="Periodo de evaluación"
                        ></v-autocomplete>
                    </v-col>

                    <v-col cols="3" >
                        <v-autocomplete
                            v-model="dependency"
                            flat
                            solo-inverted
                            hide-details
                            :items="dependencies"
                            :item-text="(depedency)=> capitalize(depedency.name)"
                            item-value="identifier"
                            prepend-inner-icon="mdi-home-search"
                            label="Dependencia"
                        ></v-autocomplete>
                    </v-col>

                    <v-col cols="3">
                        <v-autocomplete
                            v-model="functionary"
                            flat
                            solo-inverted
                            hide-details
                            :items="functionaries"
                            :item-text="(functionary)=> capitalize(functionary.name)"
                            item-value="id"
                            prepend-inner-icon="mdi-account-search"
                            label="Funcionario"
                        ></v-autocomplete>
                    </v-col>

                    <v-col cols="2">
                        <v-select
                            v-model="role"
                            flat
                            solo-inverted
                            hide-details
                            :items="roles"
                            :item-text="(role)=> capitalize(role.id)"
                            item-value="name"
                            prepend-inner-icon="mdi-eye-settings"
                            label="Rol"
                        ></v-select>
                    </v-col>

                    <v-col cols="1">
                        <v-tooltip top>
                            <template v-slot:activator="{ on, attrs }">
                                <v-btn
                                    v-on="on"
                                    v-bind="attrs"
                                    icon
                                    class="mr-2 secundario--text"
                                    @click="downloadExcel()"
                                >
                                    <v-icon>
                                        mdi-file-excel
                                    </v-icon>
                                </v-btn>
                            </template>
                            <span>Exportar resultados actuales a Excel</span>
                        </v-tooltip>
                    </v-col>
                </v-row>
            </v-toolbar>

            <!--Inicia tabla-->
            <v-card>
                <v-data-table
                    :search="search"
                    loading-text="Cargando, por favor espere..."
                    :no-data-text="noDataText"
                    :loading="isLoading"
                    :headers="headers"
                    :items="grades"
                    :items-per-page="20"
                    :footer-props="{
                        'items-per-page-options': [20,50,100,-1]
                    }"
                    class="elevation-1"
                >

                    <template
                        v-for="(competence,index) in competences"
                        v-slot:[`item.c${index+1}`]="{ item }"
                    >
                        {{item[`c${index+1}`]}}
                    </template>

                    <template v-slot:item.graph="{ item }">
                        <v-tooltip top
                        >
                            <template v-slot:activator="{on,attrs}">
                                <v-icon
                                    v-bind="attrs"
                                    v-on="on"
                                    class="mr-2 primario&#45;&#45;text"
                                    @click="setDialogToShowChart(item)"
                                >
                                    mdi-chart-line
                                </v-icon>
                            </template>
                            <span>Graficar resultados</span>
                        </v-tooltip>

                    </template>
                </v-data-table>
            </v-card>
        </v-container>


        <!--Confirmar guardar PDF-->
        <confirm-dialog
            :show="confirmSavePDF"
            @canceled-dialog="confirmSavePDF = false"
            @confirmed-dialog="savePDF()"
        >
            <template v-slot:title>
                Ahora serás redirigido a la pantalla para guardar el PDF
            </template>
            Una vez allí, lo único que debes hacer es darle click al botón de <strong class="black--text"> Guardar </strong> en la parte inferior derecha de tu pantal.la
            <template v-slot:confirm-button-text>
                Descargar PDF
            </template>
        </confirm-dialog>

    </AuthenticatedLayout>
</template>

<script>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import {InertiaLink} from "@inertiajs/inertia-vue";
import {prepareErrorText, showSnackbar} from "@/HelperFunctions"
import ConfirmDialog from "@/Components/ConfirmDialog";
import Form from "@/models/Form";
import Snackbar from "@/Components/Snackbar";
// import Chart from "chart.js/auto";
import Question from "@/models/Question";
import Papa from 'papaparse';

export default {
    components: {
        ConfirmDialog,
        AuthenticatedLayout,
        InertiaLink,
        Snackbar,
    },
    data: () => {
        return {
            sheet: false,
            //Table info
            search: '',
            headers: [],

            //Display data
            assessments: [],
            assessmentPeriod: '',
            assessmentPeriods: [],
            competences: [],
            dependency: '',
            dependencies:[],
            functionary: '',
            selectedTeacher: '',
            functionaries:[],
            selectedTeacherToGraph: '',
            selectedTeacherOpenAnswers: '',
            role:'',
            roles: [],

            grades: [],

            dataToGraph: [],
            chart:'',
            datasets:[],
            competencesValuesAsArray:[],
            responseIdeals: [],
            responseIdealsCompetences: [],
            responseIdealsCompetencesArray: [],
            openAnswersStudents: [],
            openAnswersColleagues: [],
            confirmSavePDF: false,
            //Snackbars
            snackbar: {
                text: "",
                type: 'alert',
                status: false,
                timeout: 2000,
            },
            //Dialogs
            showChartDialog: false,
            showOpenAnswersDialog: false,
            isLoading: true,
            isUserAdmin: true,
            noDataText: 'Para comenzar, selecciona un periodo de evaluación y la dependencia que deseas visualizar'
        }
    },

    props: {
        propsDependencies: Array,
        assessmentPeriodsArray: Array,
        token: String
    },

    async created() {
        await this.checkUser();
        await this.getTableHeaders();
        await this.getAssessmentPeriods();
        await this.getDependencies();
        await this.getFunctionaries();
        await this.getRoles();
        await this.getIndividualGrades();
        this.isLoading = false;
    },

    methods: {
        checkUser: function (){
            if (this.propsDependencies !== undefined){
                this.isUserAdmin = false;
                console.log(this.propsDependencies);
            }
        },

        getTableHeaders: async function (){
            this.headers.push({text:"Funcionario", value:"name", width: "15%"})
            // this.headers.push({text:"Rol", value:"dependency_role", width: "10%"})
            await this.getCompetences();
            this.headers.push({text:"Fecha de envío", value:"submitted_at", width: "15%"})
            this.headers.push({text:"Graficar resultados", value:"graph", width: "15%"})
        },

        getCompetences: async function () {
            let request = await axios.get(route('api.competences.index'));
            console.log(request.data);
            this.competences = request.data;
            this.competences.forEach(competence =>{
                this.headers.push({text:competence.name, value:`c${competence.position}`, sortable:false})
            });
        },

        getAssessmentPeriods: async function () {
            let request = await axios.get(route('api.assessmentPeriods.index'));
            this.assessmentPeriods = request.data;
        },

        getDependencies: async function (){
            let request = await axios.get(route('api.dependencies.index', {assessmentPeriodId: this.assessmentPeriod}));
            console.log(request.data, "dependencies");
            this.dependencies = this.sortArrayAlphabetically(request.data);
            this.dependencies = this.dependencies.filter(dependency => {
                return dependency.functionaries_from_dependency.length > 0;
            })
            if (!this.isUserAdmin){
                this.dependencies = this.dependencies.filter (dependency => {
                    return this.propsDependencies.includes(dependency.identifier)
                })
                return this.dependencies
            }
            this.dependencies.unshift({name: 'Todas las dependencias', identifier:''})
        },

        getFunctionaries: async function (){
            let request = await axios.get(route('api.functionaries.index', {assessmentPeriodId:this.assessmentPeriod}));
            this.functionaries = request.data;
        },

        matchProperty: function (array, propertyPath, reference) {
            return array.filter((item) => {
                const propertyArr = propertyPath.split(".");
                const propertyValue = propertyArr.reduce((obj, key) => {
                    return obj && obj[key];
                }, item);
                return propertyValue === reference;
            });
        },

        async getIndividualGrades(){
            let url = route('api.answers.index',{assessmentPeriodId: this.assessmentPeriod});
            let request = await axios.get(url);
            let individualGrades = request.data;
            console.log(request.data, "Individual grades");

            individualGrades.forEach(grade => {
                this.grades.push(grade);
            })




        },

        //
        // async getFinalGrades(){
        //     let url = route('formAnswers.finalGrades', {assessmentPeriodId: this.assessmentPeriod});
        //     let request = await axios.get(url);
        //     let finalGrades = request.data;
        //     console.log(finalGrades, 'promedio final');
        //
        //     finalGrades.forEach(answer =>{
        //         answer.unit_role = 'promedio final'
        //         answer.aggregate_students_amount_reviewers = answer.involved_actors
        //         answer.aggregate_students_amount_on_360_groups = answer.total_actors
        //         this.assessments.push(answer)
        //     });
        //     this.assessments.sort(this.orderData);
        // },

        getOpenAnswersFromColleagues: async function (teacherId){
            let url = route('formAnswers.teachers.openAnswersColleagues', {assessmentPeriodId: this.assessmentPeriod});
            let request = await axios.post(url, {teacherId: teacherId});
            this.openAnswersColleagues = request.data;
            console.log(this.openAnswersColleagues, 'respuestas de colegas');
        },


        async setDialogToShowChart(teacher){
            this.showChartDialog = true
            this.selectedTeacher = teacher;
            await this.getResponseIdealsDataset(teacher);
            this.getRolesDatasets(teacher);
            this.getGraph();
            this.getChartAsObject()
        },

        async setDialogToShowOpenAnswers(teacher){
            this.selectedTeacherOpenAnswers = teacher.name;
            this.showOpenAnswersDialog = true
            if(teacher.unit_role === 'estudiante'){
                await this.getOpenAnswersFromStudents(teacher.teacherId)
            }
            else{
                await this.getOpenAnswersFromColleagues(teacher.teacherId);
            }
        },

        async downloadPDF(){
            this.confirmSavePDF = false;
            this.datasets.forEach(dataset =>{
                dataset.fill = {target: 'origin',
                    above: 'rgb(255, 255, 255)',
                    below: 'rgb(255, 255, 255)'}
            })
            console.log(this.selectedTeacher);
            var winName='MyWindow';
            var winURL= route('reports.assessment360');
            var windowOption='resizable=yes,height=600,width=800,location=0,menubar=0,scrollbars=1';
            var params = { _token: this.token,
                chart: JSON.stringify(this.getChartAsObject()),
                teacherResults: JSON.stringify(this.filteredItems),
                teacherId: this.selectedTeacher.teacherId,
                assessmentPeriodId: this.assessmentPeriod
            };

            var form = document.createElement("form");
            form.setAttribute("method", "post");
            form.setAttribute("action", winURL);
            form.setAttribute("target",winName);
            for (var i in params) {
                if (params.hasOwnProperty(i)) {
                    var input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = i;
                    input.value = params[i];
                    form.appendChild(input);
                }
            }
            document.body.appendChild(form);
            window.open('', winName, windowOption);
            form.target = winName;
            form.submit();
            document.body.removeChild(form);
        },

        downloadExcel (){
            console.log(this.filteredItems);

            if (this.filteredItems.length === 0){
                showSnackbar(this.snackbar, "No hay datos para guardar", 'alert');
            }

            /*            let excelInfo = this.filteredItems.map(item =>{

                            return {
                                Nombre :item.name,
                                rol: item.unit_role,
                                NombreUnidad: item.unitName,
                                PromedioC1: item.first_competence_average,
                                PromedioC2: item.second_competence_average,
                                PromedioC3: item.third_competence_average,
                                PromedioC4: item.fourth_competence_average,
                                PromedioC5: item.fifth_competence_average,
                                PromedioC6: item.sixth_competence_average,
                            }
                        })

                        let csv = Papa.unparse(excelInfo, {delimiter:';'});
                        var csvData = new Blob(["\uFEFF"+csv], {type: 'text/csv;charset=utf-8;'});
                        var csvURL =  null;
                        if (navigator.msSaveBlob)
                        {
                            csvURL = navigator.msSaveBlob(csvData, 'ResultadosEvaluación.csv');
                        }
                        else
                        {
                            csvURL = window.URL.createObjectURL(csvData);
                        }

                        var tempLink = document.createElement('a');
                        tempLink.href = csvURL;
                        tempLink.setAttribute('download', 'ResultadosEvaluación.csv');
                        tempLink.click();*/
        },


        getRoles (){
            this.roles = [{id:  'Todos los roles', name: ''},{id: 'jefe', name: 'jefe'},
                {id: 'par', name: 'par'}, {id: 'autoevaluación', name: 'autoevaluación'},
                {id: 'cliente interno', name: 'cliente interno'}, {id: 'cliente externo', name: 'cliente externo'},
                {id: 'promedio final', name:'promedio final'}]
        },

        capitalize($field){
            return $field.toLowerCase().split(' ').map((word) => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
        },

        sortArrayAlphabetically(array){
            return array.sort( (p1, p2) =>
                (p1.name > p2.name) ? 1 : (p1.name > p2.name) ? -1 : 0);
        },

        getFilteredAssessmentsByDependency(assessments = null) {

            if (assessments === null) {
                assessments = this.assessments;
            }
            return assessments.filter((assessment) => {
                let doesAssessmentHaveDependency = false;
                if (assessment.dependency_identifier === this.dependency) {
                    doesAssessmentHaveDependency = true;
                }
                return doesAssessmentHaveDependency;
            });
        },

        getFilteredAssessmentsByTeacher(assessments = null) {
            if (assessments === null) {
                assessments = this.assessments;
            }
            return this.matchProperty(assessments, 'teacherId', this.teacher)
        },

        getFilteredAssessmentsByRole(assessments = null) {
            if (assessments === null) {
                assessments = this.assessments;
            }
            return this.matchProperty(assessments, 'unit_role', this.role)
        },

        fillCompetencesArray(roleArray) {
            let array = [roleArray.first_competence_average, roleArray.second_competence_average, roleArray.third_competence_average, roleArray.fourth_competence_average,
                roleArray.fifth_competence_average, roleArray.sixth_competence_average]
            return array;
        },

        getTeachingLadderNameByParameter: async function (teachingLadderCode){

            let request = await axios.get(route('api.assessmentPeriods.teachingLadders'));
            let teachingLadders = request.data
            teachingLadders.forEach(teachingLadder =>{

                if(teachingLadder == 'NIN'){

                    this.finalTeachingLadders.unshift({name : 'Ninguno',
                        identifier:teachingLadder})
                }

                if(teachingLadder == 'AUX'){

                    this.finalTeachingLadders.unshift({name : 'Auxiliar',
                        identifier:teachingLadder})
                }

                if(teachingLadder == 'ASI'){
                    this.finalTeachingLadders.unshift({name : 'Asistente',
                        identifier:teachingLadder})
                }

                if(teachingLadder == 'ASO'){
                    this.finalTeachingLadders.unshift({name : 'Asociado',
                        identifier:teachingLadder})
                }

                if(teachingLadder == 'TIT'){
                    this.finalTeachingLadders.unshift({name : 'Titular',
                        identifier:teachingLadder})
                }

            })

            let teachingLadder = this.finalTeachingLadders.find(teachingLadder =>{
                return teachingLadder.identifier == teachingLadderCode
            })

            if (teachingLadder === undefined){
                return 'Ninguno'
            }
            return teachingLadder.name

        },

        getPossibleInitialCompetences() {
            return new Question().getPossibleCompetencesAsArrayOfObjects();
        },

        async getResponseIdealsCompetences(teachingLadder, unitIdentifier){

            let url = route('teacher.responseIdeals.get');
            let request = await axios.post(url, {teachingLadder: teachingLadder, unitIdentifier: unitIdentifier, assessmentPeriodId: this.assessmentPeriod})

            console.log(request.data, 'RESPONSE IDEALS DATASET')

            if(request.data.length === 0){
                return this.getPossibleInitialCompetences();
            }
            return request.data;
        },

        async getResponseIdealsDataset(teacher){

            console.log(teacher, 'teacher');
            this.selectedTeacherToGraph = teacher.name
            let unitIdentifier = teacher.unit_identifier
            let teacherId = teacher.teacherId;
            let info = {userId : teacherId}
            let request = await axios.post(route('teachers.getTeachingLadder'), info)
            let teachingLadder= await this.getTeachingLadderNameByParameter(request.data)

            console.log(request.data, 'RESPONSE IDEALS DATASET')
            let responseIdealsCompetences = await this.getResponseIdealsCompetences(teachingLadder, unitIdentifier);

            responseIdealsCompetences.forEach(competence =>{
                this.responseIdealsCompetencesArray.push(competence.value);
            })

            this.datasets.unshift({
                label: `Nivel Esperado (${teachingLadder == 'Ninguno' ? 'Auxiliar' : teachingLadder})`,
                data: this.responseIdealsCompetencesArray,
                backgroundColor: 'orange',
                borderColor: 'orange',
                borderWidth: 2,
                borderDash: [5, 5],
            })
        },

        getRolesDatasets(teacher){

            let teacherRolesArrays = this.filteredItems.filter((item) => {
                return item.name == teacher.name
            })

            let colors = new Question().getLineChartColors();

            teacherRolesArrays.forEach(roleArray => {

                if(roleArray.unit_role == 'promedio final')
                {
                    this.datasets.push({

                        label: this.capitalize(roleArray.unit_role),
                        data: this.fillCompetencesArray(roleArray),
                        backgroundColor: 'black',
                        borderColor: 'black',
                        borderWidth: 5
                    })
                }

                else{

                    colors.forEach(color => {
                        if(color.role === roleArray.unit_role){
                            this.datasets.push({

                                label: this.capitalize(roleArray.unit_role),
                                data: this.fillCompetencesArray(roleArray),
                                backgroundColor: color.color,
                                borderColor: color.color,
                                borderWidth: 2
                            })
                        }
                    })
                }
            })
        },

        orderData(a,b){
            if ( a.name < b.name ){
                return -1;
            }
            if ( a.name > b.name ){
                return 1;
            }
            return 0;

        },

        setDialogToCancelChart (){
            this.showChartDialog = false
            this.chart.destroy();
            this.responseIdealsCompetencesArray.length = 0;
            this.finalTeachingLadders.length= 0;
            this.datasets = [];
        },

        setDialogToCancelOpenAnswers (){
            this.showOpenAnswersDialog = false;
            this.openAnswersColleagues = [];
            this.openAnswersStudents= [];
        },

        addAllElementSelectionItem(model, text) {
            model.unshift({id: '', name: text});
        },

    },

    computed: {

        filteredItems() {

            let finalAssessments = this.assessments;

            if (this.individualView){
                finalAssessments = this.getFilteredAssessmentsByDependency(finalAssessments);
            }

            else {
                if (this.unit !== '') {
                    finalAssessments = this.getFilteredAssessmentsByDependency(finalAssessments);
                }
            }

            if (this.teacher !== '') {
                finalAssessments = this.getFilteredAssessmentsByTeacher(finalAssessments);
            }
            if (this.role !== '') {
                finalAssessments = this.getFilteredAssessmentsByRole(finalAssessments);
            }

            return finalAssessments;
        },


        filteredFunctionaries(){

            let finalFunctionaries = this.functionaries;
            let finalAssessments = this.assessments;

            if (this.dependency !== '') {
                finalAssessments = this.getFilteredAssessmentsByDependency();
                finalFunctionaries = finalFunctionaries.filter((functionary) => {
                    return finalAssessments.some((assessment) => assessment.teacherId == functionary.id)
                });
            }

            this.addAllElementSelectionItem(finalFunctionaries, 'Todos los docentes');
            return finalFunctionaries;
        }
    },

    watch:{

        async assessmentPeriod(){
            await this.getDependencies();
            await this.getFunctionaries();
        }
    }
}
</script>