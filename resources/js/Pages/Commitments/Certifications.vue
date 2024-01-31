<template>
        <v-container>
            <Snackbar :timeout="snackbar.timeout" :text="snackbar.text" :type="snackbar.type"
                      :show="snackbar.status" @closeSnackbar="snackbar.status = false"></Snackbar>
            <!--Inicia tabla-->
            <v-card style="margin-bottom: 30px">
                <v-data-table
                    :search="search"
                    loading-text="Cargando, por favor espere..."
                    no-results-text="Aún no hay ningún comentario para este compromiso"
                    :loading="isLoading"
                    :headers="headers"
                    :items="files"
                    :items-per-page="20"
                    :footer-props="{
                        'items-per-page-options': [20,50,100,-1]
                    }"
                    class="elevation-1"
                >
                    <template v-slot:item.actions="{ item }">

                            <v-tooltip top>
                                <template v-slot:activator="{ on, attrs }">
                                    <form :action="route('certifications.downloadFile',{certification: item.encoded_file_name})" method="GET">
                                    <v-btn
                                        type="submit"
                                        v-on="on"
                                        v-bind="attrs"
                                        icon
                                        class="mr-2 primario--text"
                                    >
                                        <v-icon>
                                            mdi-download-circle
                                        </v-icon>
                                    </v-btn>
                                    </form>
                                </template>
                                <span>Descargar archivo</span>
                            </v-tooltip>

                        <v-tooltip bottom>
                            <template v-slot:activator="{ on, attrs }">
                                    <v-icon
                                        v-bind="attrs"
                                        v-on="on"
                                        class="mr-2 primario--text"
                                    @click="">
                                        mdi-delete
                                    </v-icon>
                            </template>
                            <span>Borrar archivo</span>
                        </v-tooltip>
                    </template>
                </v-data-table>
            </v-card>


            <!--Agregar archivos -->
            <v-dialog
                v-model="addFileDialog"
                persistent
                max-width="650px"
            >
                <v-card>
                    <v-card-title>
                        <span>
                        </span>
                        <span class="text-h5">Añadir un archivo al compromiso</span>
                    </v-card-title>
                    <v-card-text>
                        <v-container>
                            <v-row>
                                <v-col cols="12">
                                    <template>
                                        <v-file-input
                                            label="Click aquí para agregar el archivo"
                                            outlined
                                            dense
                                            accept="image/*,.pdf,.doc,.docx"
                                            @change="addFile"
                                        ></v-file-input>
                                        <h4>Formatos de archivo soportados: .pdf .doc </h4>
                                    </template>
                                </v-col>
                            </v-row>
                        </v-container>
                    </v-card-text>
                    <v-card-actions>
                        <v-spacer></v-spacer>
                        <v-btn
                            color="primario"
                            text
                            @click="addFileDialog = false"
                        >
                            Cancelar
                        </v-btn>
                        <v-btn
                            color="primario"
                            text
                            @click="checkAndSendFile"
                        >
                            Guardar archivo
                        </v-btn>
                    </v-card-actions>
                </v-card>
            </v-dialog>
        </v-container>
</template>

<script>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import {InertiaLink} from "@inertiajs/inertia-vue";
import {prepareErrorText, showSnackbar} from "@/HelperFunctions"
import ConfirmDialog from "@/Components/ConfirmDialog";
import Snackbar from "@/Components/Snackbar";
import Comment from "@/models/Comment"


export default {
    components: {
        ConfirmDialog,
        AuthenticatedLayout,
        InertiaLink,
        Snackbar,
    },

    props: {
        commitment: Object
    },

    data: () => {
        return {
            //Table info
            search: '',
            headers:[
                {text: 'Nombre de archivo', value: 'original_file_name'},
                {text: 'Añadido por', value: 'user_name', sortable: false},
                {text: 'Acciones', value: 'actions', sortable: false, width:'15%'},
                {text: 'Última actualización', value: 'updated_at', sortable: false},
            ],

            files:[],

            //Snackbars
            snackbar: {
                text: "",
                type: 'alert',
                status: false,
                timeout: 2000,
            },

            fileSelected: null,

            //Dialogs
            isLoading: true,
            addFileDialog: false,
        }
    },
    async created() {
        await this.getFiles();
        this.isLoading = false;
    },

    methods: {

       setAddFileDialog(){
            this.addFileDialog = true;
        },

        getFiles: async function (){
            let data = this.commitment
            let request = await axios.get(route('api.certifications.index', data))
            this.files = request.data;
            console.log(request.data);

        },

        addFile: function (e){

            console.log(e, "info obtenida");
            if (e === null){
                return;
            }
            this.fileSelected = e;
        },

        checkAndSendFile: async function (){

            const file = new FormData();
            file.append("file", this.fileSelected)
            file.append("commitment_id", this.commitment.id)

            try {
                let request = await axios.post(route('api.certifications.store'), file,
                    {headers:{'content-type': 'multipart/form-data'}});
                this.addFileDialog = false;
                this.fileSelected = null;
                showSnackbar(this.snackbar, request.data.message, 'success', 5000);
                await this.getFiles();
            } catch (e) {
                showSnackbar(this.snackbar, e.response.data.message, 'alert', 8000);
            }

        }

    },
}
</script>
