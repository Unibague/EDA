<template>
    <AuthenticatedLayout>
        <Snackbar :timeout="snackbar.timeout" :text="snackbar.text" :type="snackbar.type"
                  :show="snackbar.status" @closeSnackbar="snackbar.status = false"></Snackbar>

        <v-container>
            <div class="d-flex flex-column align-end mb-8">
                <h2 class="align-self-start"> Visualizando el compromiso
                    {{this.commitment.training_name}} del funcionario {{this.commitment.user_name}}</h2>
                <div>
                    <v-btn
                        color="primario"
                        class="grey--text text--lighten-4"
                        @click="setCommentDialogToCreateOrEdit('create')"
                    >
                        Agregar nuevo comentario
                    </v-btn>

                    <v-btn
                        color="primario"
                        class="grey--text text--lighten-4"
                        @click="callChildComponent"
                    >
                        Agregar nuevo archivo
                    </v-btn>
                </div>
            </div>

            <Certifications :commitment="this.commitment" ref="childComponent"> </Certifications>

            <!--Inicia tabla-->
            <v-card>
                <v-card-title>
                    <v-text-field
                        v-model="search"
                        append-icon="mdi-magnify"
                        label="Filtrar"
                        single-line
                        hide-details
                    ></v-text-field>
                </v-card-title>
                <v-data-table
                    :search="search"
                    loading-text="Cargando, por favor espere..."
                    no-results-text="Aún no hay ningún comentario para este compromiso"
                    :loading="isLoading"
                    :headers="headers"
                    :items="comments"
                    :items-per-page="20"
                    :footer-props="{
                        'items-per-page-options': [20,50,100,-1]
                    }"
                    class="elevation-1"
                >
                    <template v-slot:item.actions="{ item }">
                        <v-icon
                            class="mr-2 primario--text"
                            @click="setCommentDialogToCreateOrEdit('edit',item)"
                        >
                            mdi-pencil
                        </v-icon>

                        <v-icon
                            class="primario--text"
                            @click="confirmDeleteComment(item)"
                        >
                            mdi-delete
                        </v-icon>
                    </template>
                </v-data-table>
            </v-card>
            <!--Acaba tabla-->

            <!------------Seccion de dialogos ---------->

            <!--Crear o editar comentario -->
            <v-dialog
                v-model="createOrEditDialog.dialogStatus"
                persistent
                max-width="650px"
            >
                <v-card>
                    <v-card-title>
                        <span>
                        </span>
                        <span class="text-h5">Añadir/Editar Comentario</span>
                    </v-card-title>
                    <v-card-text>
                        <v-container>
                            <v-row>
                                <v-col cols="12">
                                    <v-textarea
                                        label="Introduzca el comentario"
                                        required
                                        v-model="$data[createOrEditDialog.model].text"
                                    ></v-textarea>
                                </v-col>
                            </v-row>
                        </v-container>
                    </v-card-text>
                    <v-card-actions>
                        <v-spacer></v-spacer>
                        <v-btn
                            color="primario"
                            text
                            @click="createOrEditDialog.dialogStatus = false"
                        >
                            Cancelar
                        </v-btn>
                        <v-btn
                            color="primario"
                            text
                            @click="handleSelectedMethod"
                        >
                            Guardar cambios
                        </v-btn>
                    </v-card-actions>
                </v-card>
            </v-dialog>


            <!--Confirmar borrar comentario-->
            <confirm-dialog
                :show="deleteCommentDialog"
                @canceled-dialog="deleteCommentDialog = false"
                @confirmed-dialog="deleteComment(deletedCommentId)"
            >
                <template v-slot:title>
                    Estás a punto de eliminar el comentario seleccionado
                </template>

                ¡Cuidado! esta acción es irreversible

                <template v-slot:confirm-button-text>
                    Borrar
                </template>
            </confirm-dialog>
        </v-container>
    </AuthenticatedLayout>
</template>

<script>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import {InertiaLink} from "@inertiajs/inertia-vue";
import {prepareErrorText, showSnackbar} from "@/HelperFunctions"
import ConfirmDialog from "@/Components/ConfirmDialog";
import Snackbar from "@/Components/Snackbar";
import Comment from "@/models/Comment"
import Certifications from "@/Pages/Commitments/Certifications";


export default {
    components: {
        Certifications,
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
            headers: [
                {text: 'Comentario', value: 'text'},
                {text: 'Acciones', value: 'actions', sortable: false},
                {text: 'Publicado por', value: 'user_name', sortable: false},
                {text: 'Última actualización', value: 'updated_at', sortable: false},
            ],

            comments: [],
            //AssessmentPeriods models
            newComment: new Comment(),
            editedComment: new Comment(),
            deletedCommentId: 0,
            //Snackbars
            snackbar: {
                text: "",
                type: 'alert',
                status: false,
                timeout: 2000,
            },


            //Dialogs
            deleteCommentDialog: false,
            createOrEditDialog: {
                model: 'newComment',
                method: 'createComment',
                dialogStatus: false,
            },

            isLoading: true,

        }
    },
    async created() {
        await this.getComments();
        this.isLoading = false;
    },

    methods: {

        callChildComponent(){
            this.$refs.childComponent.setAddFileDialog();
        },

        getComments: async function () {
            let data = this.commitment;
            let request = await axios.get(route('api.comments.index', data));
            console.log(request.data);
            this.comments = request.data;
        },
        createComment: async function () {
            if (this.newComment.hasEmptyProperties()) {
                showSnackbar(this.snackbar, 'Debes diligenciar todos los campos obligatorios', 'red', 2000);
                return;
            }

            this.newComment.commitment_id = this.commitment.id;
            this.newComment.posted_by = this.$page.props.user.id;

            let data = this.newComment.toObjectRequest();
            //Clear competence information
            this.newComment = new Comment();

            try {
                let request = await axios.post(route('api.comments.store'), data);
                this.createOrEditDialog.dialogStatus = false;
                showSnackbar(this.snackbar, request.data.message, 'success', 2000);
                await this.getComments();
            } catch (e) {
                showSnackbar(this.snackbar, e.response.data.message, 'alert', 3000);
            }
        },

        editComment: async function () {
            //Verify request
            if (this.editedComment.hasEmptyProperties()) {
                showSnackbar(this.snackbar, 'Debes diligenciar todos los campos obligatorios', 'red', 2000);
                return;
            }
            //Recollect information
            let data = this.editedComment.toObjectRequest();

            try {
                let request = await axios.patch(route('api.comments.update', {'comment': this.editedComment.id}), data);
                this.createOrEditDialog.dialogStatus = false;
                showSnackbar(this.snackbar, request.data.message, 'success');
                await this.getComments();
                //Clear role information
                this.editedComment = new Comment();
            } catch (e) {
                showSnackbar(this.snackbar, prepareErrorText(e), 'alert');
            }
        },

        confirmDeleteComment: function (comment) {
            this.deletedCommentId = comment.id;
            this.deleteCommentDialog = true;
        },

        deleteComment: async function (comment) {
            try {
                let request = await axios.delete(route('api.comments.destroy', {comment: comment}));
                this.deleteCommentDialog = false;
                showSnackbar(this.snackbar, request.data.message, 'success');
                await this.getComments();
            } catch (e) {
                showSnackbar(this.snackbar, e.response.data.message, 'red', 3000);
            }

        },
        handleSelectedMethod: function () {
            this[this.createOrEditDialog.method]();
        },

        setCommentDialogToCreateOrEdit(which, item = null) {
            if (which === 'create') {
                this.createOrEditDialog.method = 'createComment';
                this.createOrEditDialog.model = 'newComment';
                this.createOrEditDialog.dialogStatus = true;
            }

            if (which === 'edit') {
                this.editedComment = Comment.fromModel(item);
                this.createOrEditDialog.method = 'editComment';
                this.createOrEditDialog.model = 'editedComment';
                this.createOrEditDialog.dialogStatus = true;
            }

        }

    },
}
</script>
