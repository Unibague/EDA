<template>
    <AuthenticatedLayout>
        <Snackbar :timeout="snackbar.timeout" :text="snackbar.text" :type="snackbar.type"
                  :show="snackbar.status" @closeSnackbar="snackbar.status = false"></Snackbar>

        <v-container>
            <div class="d-flex flex-column align-end mb-8">
                <h2 class="align-self-start" > {{this.dependency.name}} </h2>

<!--                <div>
                    <InertiaLink :href="route('units.assessment.status',{unitId:this.currentUnit.identifier}) ">
                        <v-btn>
                            Estado de la evaluación
                        </v-btn>
                    </InertiaLink>

                    <InertiaLink :href="route('units.roles.manage',{unitId:this.currentUnit.identifier}) ">
                        <v-btn class="ml-4">
                            Gestionar Evaluadores
                        </v-btn>
                    </InertiaLink>
                    <v-btn
                        color="primario"
                        class="grey&#45;&#45;text text&#45;&#45;lighten-4 ml-4"
                        @click="setDialogToAddUnitAdmin"
                    >
                        Añadir Administrador
                    </v-btn>

                    <v-btn
                        color="primario"
                        class="grey&#45;&#45;text text&#45;&#45;lighten-4 ml-4"
                        @click="setDialogToAddUnitBoss"
                    >
                        Añadir Jefe de docente
                    </v-btn>
                </div>-->
            </div>


<!--            <h3 class="mb-4"> Administradores y jefes de docente</h3>

            &lt;!&ndash;Inicia tabla de admins de unidad y jefes de profesores&ndash;&gt;
            <v-card>

                <v-data-table
                    loading-text="Cargando, por favor espere..."
                    :headers="headersAdminsAndBosses"
                    :items="adminsAndBosses"
                    :items-per-page="20"
                    :footer-props="{
                        'items-per-page-options': [10,20,30,-1]
                    }"
                    class="elevation-1"
                >

                    <template v-slot:item.role="{ item }">
                        {{ item.pivot.role_id == 2 ? 'Administrador de unidad' : 'Jefe de profesor' }}
                    </template>

                    <template v-slot:item.action="{ item }">

                        <v-tooltip top v-if="item.pivot.role_id == 2" >
                            <template v-slot:activator="{on,attrs}">

                                <v-icon
                                    v-bind="attrs"
                                    v-on="on"
                                    class="primario&#45;&#45;text align-center"
                                    @click="confirmDeleteUnitAdmin(item.id)"
                                >
                                    mdi-delete
                                </v-icon>

                            </template>
                            <span>Eliminar administrador de unidad</span>
                        </v-tooltip>


                        <v-tooltip top v-if="item.pivot.role_id == 3" >
                            <template v-slot:activator="{on,attrs}">

                                <v-icon
                                    v-bind="attrs"
                                    v-on="on"
                                    class="primario&#45;&#45;text align-center"
                                    color="red"
                                    @click="confirmDeleteUnitBoss(item.id)"
                                >
                                    mdi-delete
                                </v-icon>

                            </template>
                            <span>Eliminar jefe de docente</span>
                        </v-tooltip>
                    </template>
                </v-data-table>
            </v-card>-->

            <h3 class="mt-9"> Funcionarios en la dependencia</h3>

            <!--Tabla profesores de la unidad-->
            <v-card class="mt-4">
                <v-data-table
                    loading-text="Cargando, por favor espere..."
                    :headers="headersFunctionaries"
                    :items="functionaries"
                    :items-per-page="20"
                    :footer-props="{
                        'items-per-page-options': [10,20,30,-1]
                    }"
                    class="elevation-1"
                >

<!--                    <template v-slot:item.actions="{ item }">-->
<!--                        <v-tooltip>-->
<!--                            <template v-slot:activator="{on,attrs}">-->
<!--                                <v-icon-->
<!--                                    v-bind="attrs"-->
<!--                                    v-on="on"-->
<!--                                    class="primario&#45;&#45;text align-center"-->
<!--                                    @click="setDialogToTransferTeacher(item)"-->
<!--                                >-->
<!--                                    mdi-swap-horizontal-->
<!--                                </v-icon>-->
<!--                            </template>-->
<!--                            <span>Gestionar asignaciones para el funcionario</span>-->
<!--                        </v-tooltip>-->
<!--                    </template>-->
                </v-data-table>
            </v-card>


            <!--Seccion de dialogos-->

            <!--Transferir docente entre unidades-->

<!--            <v-dialog
                v-model="transferTeacherDialog"
                persistent
                max-width="650px"
            >
                <v-card>
                    <v-card-title>
                        <span>
                        </span>
                        <span class="text-h4-border">Transfiriendo al docente {{this.selectedTeacherName}}</span>
                    </v-card-title>
                    <v-card-text>
                        <span>Seleccione la unidad donde desea transferir al docente</span>
                        <v-container>
                            <v-row>
                                <v-col cols="12">
                                    <v-autocomplete
                                        label="Nombre de la unidad"
                                        v-model="selectedUnit"
                                        :items="units"
                                        item-text="title"
                                        return-object
                                        single-line
                                    ></v-autocomplete>
                                </v-col>
                            </v-row>
                        </v-container>
                        <small>Los campos con * son obligatorios </small>
                    </v-card-text>
                    <v-card-actions>
                        <v-spacer></v-spacer>

                        <v-btn
                            color="primario"
                            text
                            @click="transferTeacherDialog = false"
                        >
                            Cancelar
                        </v-btn>
                        <v-btn
                            color="primario"
                            text
                            @click="transferTeacherToSelectedUnit(selectedUnit.value)"
                        >
                            Guardar cambios
                        </v-btn>
                    </v-card-actions>
                </v-card>
            </v-dialog>-->


            <!--Asignar Admin de unidad-->
            <!--         <v-dialog
                         v-model="unitAdminDialog"
                         persistent
                         max-width="650px"
                     >
                         <v-card>
                             <v-card-title>
                                 <span>
                                 </span>
                                 <span class="text-h4-border"> Añadir adminsitrador a la unidad </span>
                             </v-card-title>
                             <v-card-text>
                                 <span>Ingrese el nombre del funcionario</span>

                                 <v-autocomplete
                                     label="Por favor selecciona un usuario"
                                     :items="listOfStaffMembers"
                                     v-model="unitAdmin"
                                     item-text="name"
                                     item-value="id"
                                     return-object
                                 ></v-autocomplete>


                                 <small>Los campos con * son obligatorios </small>
                             </v-card-text>
                             <v-card-actions>
                                 <v-spacer></v-spacer>

                                 <v-btn
                                     color="primario"
                                     text
                                     @click="cancelUnitAdminDialog()"
                                 >
                                     Cancelar
                                 </v-btn>
                                 <v-btn
                                     color="primario"
                                     text
                                     @click="assignStaffMemberAsAdmin(unitAdmin.id)"
                                 >
                                     Guardar cambios
                                 </v-btn>
                             </v-card-actions>
                         </v-card>
                     </v-dialog>

                     <--Asignar jefe de docente-->

<!--            <v-dialog
                v-model="unitBossDialog"
                persistent
                max-width="650px"
            >
                <v-card>
                    <v-card-title>
                        <span>
                        </span>
                        <span class="text-h4-border"> Añadir jefe de docente a la unidad </span>
                    </v-card-title>
                    <v-card-text>
                        <span>Ingrese el nombre del docente</span>

                        <v-autocomplete
                            label="Por favor selecciona un usuario"
                            :items="listOfStaffMembers"
                            v-model="unitBoss"
                            item-text="name"
                            item-value="id"
                            return-object
                        ></v-autocomplete>

                        <small>Los campos con * son obligatorios </small>
                    </v-card-text>
                    <v-card-actions>
                        <v-spacer></v-spacer>

                        <v-btn
                            color="primario"
                            text
                            @click="cancelUnitBossDialog()"
                        >
                            Cancelar
                        </v-btn>
                        <v-btn
                            color="primario"
                            text
                            @click="assignTeacherAsBoss(unitBoss.id)"
                        >
                            Guardar cambios
                        </v-btn>
                    </v-card-actions>
                </v-card>
            </v-dialog>

            <confirm-dialog
                :show="deleteUnitAdminDialog"
                @canceled-dialog="deleteUnitAdminDialog = false"
                @confirmed-dialog="deleteUnitAdmin()"
                max-width="600px"
            >
                <template v-slot:title>
                    Estás a punto de eliminar al administrador seleccionado
                </template>

                <h4 class="mt-2"> Ten cuidado, esta acción es irreversible </h4>

                <template v-slot:confirm-button-text>
                    Borrar
                </template>
            </confirm-dialog>

            <confirm-dialog
                :show="deleteUnitBossDialog"
                @canceled-dialog="deleteUnitBossDialog = false"
                @confirmed-dialog="deleteUnitBoss()"
            >
                <template v-slot:title>
                    Estás a punto de eliminar al jefe de docente escogido
                </template>

                <h4 class="mt-2"> Ten cuidado, esta acción es irreversible </h4>

                <template v-slot:confirm-button-text>
                    Borrar
                </template>
            </confirm-dialog>

            <confirm-dialog
                :show="sureDeleteUnitBossDialog"
                @canceled-dialog="sureDeleteUnitBossDialog = false"
                @confirmed-dialog="sureDeleteUnitBoss()"
            >
                <template v-slot:title>
                    Ese jefe ya tiene asignaciones realizadas en esta unidad. <br>¿Confirmas que deseas eliminarlo?
                </template>

                <h4 class="mt-2"> Ten cuidado, esta acción es irreversible </h4>

                <template v-slot:confirm-button-text>
                    Borrar
                </template>
            </confirm-dialog>-->
        </v-container>
    </AuthenticatedLayout>
</template>

<script>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import {InertiaLink} from "@inertiajs/inertia-vue";
import ConfirmDialog from "@/Components/ConfirmDialog";
import Snackbar from "@/Components/Snackbar";
import {showSnackbar} from "@/HelperFunctions";

export default {
    components: {
        ConfirmDialog,
        AuthenticatedLayout,
        InertiaLink,
        Snackbar,
    },


    props: {
        dependency: Object
    },

    data: () => {
        return {
            //Table info

            functionaries: [],

            headersFunctionaries: [
                {text: 'Nombre', value: 'name'},
                {text: 'Cargo', value: 'job_title'},
                {text: 'Asignaciones del funcionario', value: 'actions', width: '10%', sortable: false},
            ],

            //Snackbars
            snackbar: {
                text: "",
                type: 'alert',
                status: false,
                timeout: 2000,
            },

            isLoading: true,
        }
    },


    async created() {
        await this.getFunctionariesFromDependency();
    },

    methods:{

        async getFunctionariesFromDependency() {
            let request = await axios.get(route('api.functionaries.index', {dependency:this.dependency}));
            console.log(request.data);
            this.functionaries = request.data;
        },


        async getAllTeachersAndSortAlphabetically () {

            let url = route('teachers.getSuitableList')
            let request = await axios.get(url);
            let data = request.data;
            data.forEach(teacher => {
                this.listOfTeachers.push({
                    name: this.capitalize(teacher.user.name),
                    id: teacher.user.id
                })
            })
        },


        async getAllUnitsAndSortAlphabetically (){

            let request = await axios.get(route('api.units.index'));
            this.listOfUnits = request.data;
            this.sortArrayAlphabetically(this.listOfUnits);
            this.listOfUnits.forEach(unit => {
                this.units.push({
                    title: this.capitalize(unit.name),
                    value: unit.identifier
                })
            })
        },

        capitalize($field){
            return $field.toLowerCase().split(' ').map((word) => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
        },

        async setDialogToAddUnitAdmin(){

            this.unitAdminDialog = true
            await this.getStaffMembersAndSortAlphabetically();

        },

        async setDialogToAddUnitBoss(){
            await this.getStaffMembersAndSortAlphabetically();
            this.unitBossDialog = true
        },


        sortArrayAlphabetically(array){
            return array.sort( (p1, p2) =>
                (p1.name > p2.name) ? 1 : (p1.name > p2.name) ? -1 : 0);
        },

        assignTeacherAsBoss: async function (teacherId){

            try {
                let data = {
                    unitIdentifier: this.currentUnit.identifier,
                    userId: teacherId,
                    role: 'jefe de profesor'
                }

                let request = await axios.post(route('api.units.assignUnitBoss'), data);

                this.unitBossDialog= false;
                this.unitBoss= {name: '', id:''};
                await this.getAdminsAndBosses();
                showSnackbar(this.snackbar, request.data.message, 'success');
            }

            catch (e){
                showSnackbar(this.snackbar, e.response.data.message, 'red', 5000);
            }

        },

        cancelUnitBossDialog(){
            this.unitBossDialog = false;
        },

        cancelUnitAdminDialog(){
            this.unitAdmin= {name: '', id:''};
            this.unitAdminDialog = false;
        },

        confirmDeleteUnitAdmin: function (userId) {
            this.deletedUnitAdminId = userId;
            console.log(this.deletedUnitAdminId);
            this.deleteUnitAdminDialog = true;
        },

        confirmDeleteUnitBoss: function (userId){
            this.deletedUnitBossId = userId;
            console.log(this.deletedUnitAdminId);
            this.deleteUnitBossDialog = true;

        },

        deleteUnitBoss: async function(){

            let data = {
                unitIdentifier: this.currentUnit.identifier,
                userId: this.deletedUnitBossId
            }

            try{

                let url = route('unit.deleteUnitBoss');
                let request = await axios.post(url, data);
                this.deleteUnitBossDialog = false;
                await this.getAdminsAndBosses();
                showSnackbar(this.snackbar, request.data.message, 'success');
            }

            catch(e){
                this.deleteUnitBossDialog = false;
                this.sureDeleteUnitBossDialog = true
            }
        },

        sureDeleteUnitBoss: async function(){

            let data = {
                unitIdentifier: this.currentUnit.identifier,
                userId: this.deletedUnitBossId
            }

            try{

                let url = route('unit.confirmDeleteUnitBoss');
                let request = await axios.post(url, data);
                this.sureDeleteUnitBossDialog = false;
                await this.getAdminsAndBosses();
                showSnackbar(this.snackbar, request.data.message, 'success');
            }

            catch(e){
                showSnackbar(this.snackbar, e.response.data.message, 'red', 5000);
            }
        }
    },
}
</script>

