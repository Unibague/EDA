<template>
    <GeneralLayout>
            <div style="margin:auto">
                <v-row>
                    <v-col cols="6" class="d-flex justify-center" align-self="center">
                        <v-layout>
                            <v-flex xs12 sm8 md12>
                                <v-card class="elevation-1 pa-5">
                                    <p class="text-h5 text-center">
                                        Si eres funcionario de la Universidad, por favor ingresa con tu correo en el siguiente botón
                                    </p>
                                    <v-card-actions class="justify-center">
                                        <v-btn
                                            color="primario"
                                            class="grey--text text--lighten-4"
                                            @click="googleRedirect"
                                        >
                                            Ingresar
                                        </v-btn>
                                    </v-card-actions>
                                </v-card>
                            </v-flex>
                        </v-layout>
                    </v-col>

                    <v-spacer></v-spacer>

                    <v-col cols="6" class="d-flex justify-center" align-self="center">
                        <v-layout>
                            <v-flex xs12 sm8 md12>
                                <v-card class="elevation-12">
                                    <v-toolbar dark color="primary">
                                        <v-toolbar-title>Empresas</v-toolbar-title>
                                    </v-toolbar>
                                    <v-card-text>
                                        <form @submit.prevent="submit">
                                            <v-text-field
                                                v-model="form.email"
                                                name="username"
                                                label="Usuario"
                                                type="text"
                                                placeholder="Usuario"
                                                required
                                            ></v-text-field>

                                            <v-text-field
                                                v-model="form.password"
                                                name="password"
                                                label="Contraseña"
                                                type="password"
                                                placeholder="Contraseña"
                                                required
                                            ></v-text-field>
                                            <v-btn type="submit" class="mt-4" color="primary" value="log in">Ingresar</v-btn>
                                        </form>
                                    </v-card-text>
                                </v-card>
                            </v-flex>
                        </v-layout>
                    </v-col>
                </v-row>
            </div>



    </GeneralLayout>
</template>

<script>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import {InertiaLink} from "@inertiajs/inertia-vue";
import ConfirmDialog from "@/Components/ConfirmDialog";
import GeneralLayout from "@/Layouts/GeneralLayout";

export default {

    components: {
        GeneralLayout,
        AuthenticatedLayout,
        InertiaLink,
        ConfirmDialog
    },

    data() {
        return {
            form: this.$inertia.form({
                email: '',
                password: '',
                remember: true
            }),

            showDialog: false,
            // username: "",
            // password: "",
        };
    },

    methods: {
        submit() {
            this.form
                .transform(data => ({
                    ... data,
                    remember: this.form.remember ? 'on' : ''
                }))
                .post(this.route('login'), {
                    onFinish: () => this.form.reset('password'),
                })
        },

        async googleRedirect(){

            await axios.get(route('login'));


        }
    }
}
</script>
