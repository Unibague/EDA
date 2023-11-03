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


            <h3 class="mt-9"> Funcionarios en la dependencia</h3>
            <!--Tabla funcionarios de la dependencia-->
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

                    <template v-slot:item.actions="{item}">
                        <v-tooltip top>
                            <template v-slot:activator="{on,attrs}">
                                <InertiaLink :href="route('api.functionaryProfiles.edit', {functionaryProfile:item.functionary_profile_id, dependency: dependency.identifier})">
                                    <v-icon
                                        v-bind="attrs"
                                        v-on="on"
                                        class="mr-2 primario--text"
                                    >
                                        mdi-clipboard-check
                                    </v-icon>
                                </InertiaLink>
                            </template>
                            <span>Gestionar asignaciones</span>
                        </v-tooltip>
                    </template>
                </v-data-table>
            </v-card>
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
                {text: 'Jefe', value: 'boss'},
                {text: 'Par', value: 'peer'},
                {text: 'Cliente Interno/Externo', value: 'client'},
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
            console.log(this.dependency);
            this.functionaries = request.data;
        },

        capitalize($field){
            return $field.toLowerCase().split(' ').map((word) => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
        },

    },
}
</script>

