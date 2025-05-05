<template>
    <GeneralLayout>
            <div style="margin:auto">
                <v-row>

                    <!-- Login Funcionarios -->
                    <v-col cols="6" class="d-flex justify-center" align-self="center">
                        <v-layout >
                            <v-flex xs12 sm8 md12 >
                                <v-card class="elevation-12">
                                    <v-toolbar dark color="primario">
                                        <v-toolbar-title>Funcionarios</v-toolbar-title>
                                    </v-toolbar>
                                    <v-card-text class="text-center">
                                        <p class="text-h6 mb-4">
                                            Si eres funcionario de la Universidad, ingresa a continuación con tu cuenta institucional
                                        </p>

                                        <a :href="route('googleLogin')" style="text-decoration: none">
                                            <v-btn color="white" class="elevation-2" style="text-transform: none; color: #444;" block>
                                                <v-avatar left size="24" class="mr-2">
                                                    <img src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg" alt="Google" />
                                                </v-avatar>
                                                Ingresar con Google
                                            </v-btn>
                                        </a>

                                    </v-card-text>
                                </v-card>
                            </v-flex>
                        </v-layout>
                    </v-col>

                    <v-spacer></v-spacer>

                    <v-col cols="6" class="d-flex justify-center" align-self="center">
                        <v-layout>
                            <v-flex xs12 sm8 md12>
                                <v-card class="elevation-12">
                                    <v-toolbar dark color="primario">
                                        <v-toolbar-title>Empresas / Clientes Externos </v-toolbar-title>
                                    </v-toolbar>
                                    <v-card-text>
                                        <form @submit.prevent="submit">
                                            <v-text-field
                                                v-model="form.email"
                                                name="username"
                                                label="Usuario (correo electrónico)"
                                                type="text"
                                                placeholder="Correo"
                                                required
                                            ></v-text-field>
                                            <div v-if="form.errors.email" class="text-red-500 text-xs mt-1" style="color: red">
                                                {{form.errors.email}}
                                            </div>

                                            <v-text-field
                                                v-model="form.password"
                                                name="password"
                                                label="Contraseña"
                                                type="password"
                                                placeholder="Contraseña"
                                                required
                                            ></v-text-field>
                                            <div v-if="form.errors.password" class="text-red-500 text-xs mt-1" style="color: red">
                                                {{form.errors.password}}
                                            </div>
                                            <div v-if="form.errors.approve" class="text-red-500 text-xs mt-1" style="color: red">
                                                {{form.errors.approve}}
                                            </div>
                                            <v-btn type="submit" class="mt-4 white--text" color="primario" value="log in">Ingresar</v-btn>
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
            this.form.post(route('externalClient.login'));
        }
    }
}
</script>
