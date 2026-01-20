<template>
    <GeneralLayout>
        <div
            style="
                min-height: calc(100vh - 64px - 40px);
                display: flex;
                align-items: center;
                justify-content: center;
                width: 100%;
            "
        >
            <v-card
                class="elevation-12"
                style="max-width: 420px; width: 100%;"
            >
                <v-toolbar dark color="primario">
                    <v-toolbar-title>Funcionarios</v-toolbar-title>
                </v-toolbar>

                <v-card-text class="text-center">
                    <p class="text-h6 mb-4">
                        Ingresa a continuación con tu cuenta institucional
                    </p>

                    <a :href="route('googleLogin')" style="text-decoration: none">
                        <v-btn
                            color="white"
                            class="elevation-2"
                            style="text-transform: none; color: #444;"
                            block
                        >
                            <v-avatar left size="24" class="mr-2">
                                <img
                                    src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg"
                                    alt="Google"
                                />
                            </v-avatar>
                            Ingresar con Google
                        </v-btn>
                    </a>
                </v-card-text>
            </v-card>
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
