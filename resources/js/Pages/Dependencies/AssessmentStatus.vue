<template>
    <AuthenticatedLayout>
        <Snackbar :timeout="snackbar.timeout" :text="snackbar.text" :type="snackbar.type"
                  :show="snackbar.status" @closeSnackbar="snackbar.status = false"></Snackbar>

        <v-container>
            <div class="d-flex flex-column align-end mb-8">
                <h2 class="align-self-start">
                    <span v-if="dependency">Estado de evaluación para la dependencia {{ dependency.name }}</span>
                    <span v-else>Estado de evaluación para todas las dependencias</span>
                </h2>
            </div>

            <!-- Campo de búsqueda -->
            <v-text-field
                v-model="search"
                append-icon="mdi-magnify"
                label="Buscar"
                single-line
                hide-details
                class="mb-4"
            />

            <!--Inicia tabla-->
            <v-card>
                <v-data-table
                    :search="search"
                    loading-text="Cargando, por favor espere..."
                    :loading="isLoading"
                    :headers="headers"
                    :items="assessments"
                    :items-per-page="20"
                    :footer-props="{
                        'items-per-page-options': [20,50,100,-1]
                    }"
                    class="elevation-1"
                >
                    <template v-slot:item.peer="{ item }">
                        {{item.peer ? item.peer : ""}}
                        <v-icon
                            color="green"
                            v-if="(item.peer !== '' && item.peerPending === 0)"
                        >
                            mdi-check-bold
                        </v-icon>

                        <v-icon
                            color="red"
                            v-else
                        >
                            mdi-close-thick
                        </v-icon>
                    </template>

                    <template v-slot:item.boss="{ item }">
                        {{item.boss ? item.boss : ""}}
                        <v-icon
                            color="green"
                            v-if="(item.boss !=='' && item.bossPending === 0 )"
                        >
                            mdi-check-bold
                        </v-icon>

                        <v-icon
                            color="red"
                            v-else
                        >
                            mdi-close-thick
                        </v-icon>
                    </template>

                    <template v-slot:item.client="{ item }">
                        {{item.client ? item.client : ""}}
                        <v-icon
                            color="green"
                            v-if="(item.client !=='' && item.clientPending === 0 )"
                        >
                            mdi-check-bold
                        </v-icon>

                        <v-icon
                            color="red"
                            v-else
                        >
                            mdi-close-thick
                        </v-icon>
                    </template>

                    <template v-slot:item.auto="{ item }">
                        <v-icon
                            color="red"
                            v-if="(item.autoPending === 1)"
                        >
                            mdi-close-thick
                        </v-icon>
                        <v-icon
                            color="green"
                            v-else
                        >
                            mdi-check-bold
                        </v-icon>
                    </template>

                </v-data-table>
            </v-card>
        </v-container>
    </AuthenticatedLayout>
</template>

<script>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import {InertiaLink} from "@inertiajs/inertia-vue";
import {prepareErrorText, showSnackbar} from "@/HelperFunctions"
import ConfirmDialog from "@/Components/ConfirmDialog";
import AssessmentPeriod from "@/models/AssessmentPeriod";
import Snackbar from "@/Components/Snackbar";
import Position from "@/models/Position";

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
            search: '',
            headers: [
                {text: 'Nombre', value: 'name', align: 'center'},
                {text: 'Jefe', value: 'boss'},
                {text: 'Par', value: 'peer'},
                {text: 'Cliente Interno / Externo', value: 'client'},
                {text: 'Autoevaluación', value: 'auto'}
            ],
            assessments: [],
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
        await this.getAssessmentsFromDependency();
        this.isLoading = false;
    },

    methods: {

        async getAssessmentsFromDependency() {
            let request = await axios.get(route('api.dependencies.assessmentStatus', {dependency:this.dependency}));
            this.assessments = request.data;
        },

    },
}
</script>
