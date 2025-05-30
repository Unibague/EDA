<template>
    <GeneralLayout>
        <v-snackbar
            v-model="snackbar.status"
            :timeout="snackbar.timeout"
            color="red accent-2"
            top
            right
        >
            {{ snackbar.text }}
        </v-snackbar>

        <form :action="route('logout')" method="post" ref="logoutform">
            <input type="hidden" name="_token" :value="csrf">
        </form>

        <template v-slot:custom-v-app-bar-icon>
            <v-app-bar-nav-icon @click="drawer = true" class="white--text"></v-app-bar-nav-icon>
        </template>

        <template v-slot:app-bar-content>
            <template v-for="menuItem in menu"
                      v-if="$page.props.user.customRoleId >= menuItem.role">

                <Link as="v-btn" text v-if="!menuItem.method" :key="menuItem.title"
                      class="d-none d-md-block"
                      :class="{
                        'active-button':route().current(menuItem.routeName),
                        'normal-button':!(route().current(menuItem.routeName))
                        }"
                      :href="menuItem.href">
                    {{ menuItem.name }}
                </Link>

                <v-btn text v-else
                       :key="menuItem.title"
                       class="d-none d-md-block"
                       :class="{
                        'active-button':route().current(menuItem.routeName),
                        'normal-button':!(route().current(menuItem.routeName))
                        }"
                       @click="triggerFunction(menuItem.method)">
                    {{ menuItem.name }}
                </v-btn>
            </template>

            <template
                v-for="dropdown in dropdowns"
                v-if="$page.props.user.customRoleId === dropdown.role">
                <v-menu
                    bottom
                    origin="center center"
                >
                    <template v-slot:activator="{ on, attrs }">
                        <v-btn text
                               v-bind="attrs"
                               v-on="on"
                               class="d-none d-md-block normal-button mr-3">
                            {{ dropdown.name }}
                        </v-btn>
                    </template>

                    <v-list>
                        <Link as="v-list-item"
                              v-if="!item.method"
                              :href="item.href"
                              v-for="item in dropdown.items"
                              :key="item.name"
                        >
                            <v-list-item-title>{{ item.name }}</v-list-item-title>
                        </Link>

                        <v-list-item
                            v-else
                            :key="item.name"
                            @click="triggerFunction(item.method)"
                        >
                            <v-list-item-title>{{ item.name }}</v-list-item-title>
                        </v-list-item>
                    </v-list>
                </v-menu>
            </template>


            <v-menu left bottom>
                <template v-slot:activator="{ on, attrs }">
                    <v-avatar
                        v-bind="attrs"
                        v-on="on"
                        color="grey lighten-1"
                        size="32"
                    >
                        <span class="grey--text text--darken-4">{{ initials }}</span>
                    </v-avatar>
                </template>

                <v-list>
                    <v-list-item>
                        <v-list-item-title>
                            {{ $page.props.user.name }}
                        </v-list-item-title>
                    </v-list-item>
                    <v-list-item class="cursor-pointer" @click="logout">
                        <v-list-item-title>Cerrar sesion</v-list-item-title>
                    </v-list-item>
                </v-list>
            </v-menu>
        </template>

        <!--Barra lateral-->
        <template v-slot:custom-navigation-drawer>
            <v-navigation-drawer
                v-model="drawer"
                app
                temporary
                bottom
            >
                <v-list
                    nav
                    dense
                >
                    <v-list-item-group
                        v-model="group"
                        active-class="deep-purple--text text--accent-4"
                    >
                        <template v-for="item in menu"
                                  v-if="$page.props.user.customRoleId >= item.role">

                            <!-- LINKS DE MENU INDIVIDUALES-->

                            <Link as="v-list-item" :key="menu.name" :href="item.href"
                                  v-if="!item.method">
                                <v-list-item-icon>
                                    <v-icon>{{ item.icon }}</v-icon>
                                </v-list-item-icon>
                                <v-list-item-title>{{ item.name }}</v-list-item-title>
                            </Link>

                            <v-list-item :key="menu.name" @click="triggerFunction(item.method)"
                                         v-else>
                                <v-list-item-icon>
                                    <v-icon>{{ item.icon }}</v-icon>
                                </v-list-item-icon>
                                <v-list-item-title>{{ item.name }}</v-list-item-title>
                            </v-list-item>
                        </template>


                        <!-- LINKS DE DROPDOWNS-->
                        <v-list-group
                            v-for="dropdown in dropdowns"
                            :key="dropdown.title"
                            v-model="dropdown.active"
                            :prepend-icon="dropdown.icon"
                            no-action
                            v-if="$page.props.user.customRoleId === dropdown.role"

                        >
                            <!-- activator -->
                            <template v-slot:activator>
                                <v-list-item-content>
                                    <v-list-item-title v-text="dropdown.name"></v-list-item-title>
                                </v-list-item-content>
                            </template>


                            <template v-for="dropdownItem in dropdown.items"
                                      v-if="$page.props.user.customRoleId === dropdownItem.role">

                                <Link as="v-list-item"
                                      v-if="!dropdownItem.method"
                                      :key="dropdownItem.name"
                                      :href="dropdownItem.href"
                                >
                                    <v-list-item-content>
                                        <v-list-item-title v-text="dropdownItem.name"></v-list-item-title>
                                    </v-list-item-content>
                                </Link>

<!--                                <v-list-item
                                    @click="triggerFunction(dropdownItem.method)"
                                    :key="dropdownItem.name"
                                >
                                    <v-list-item-content  >
                                        <v-list-item-title v-text="dropdownItem.name"></v-list-item-title>
                                    </v-list-item-content>
                                </v-list-item>-->
                            </template>

                        </v-list-group>
                    </v-list-item-group>
                </v-list>

                <template v-slot:append>
                    <p class="text-center">
                        {{ $page.props.user.name }}
                    </p>
                    <div class="pa-2">
                        <v-btn block @click="logout" color="grey darken-4">
                             <span class="grey--text text--lighten-1">
                                Cerrar sesión
                             </span>
                        </v-btn>
                    </div>
                </template>
            </v-navigation-drawer>
        </template>
        <!--This is the main slot, it will contain all application content-->
        <slot></slot>


    </GeneralLayout>
</template>

<script>
import GeneralLayout from "@/Layouts/GeneralLayout";
import {Link} from '@inertiajs/inertia-vue';

export default {
    components: {
        GeneralLayout,
        Link
    },
    data: () => ({

        snackbar: {
            text: '...',
            status: false,
            timeout: 3000
        },

        csrf: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),

        drawer: false,
        menu:
            [{
                name: 'Cambiar Rol',
                href: route('pickRole'),
                role: 3,
                icon: 'mdi-calendar'
            },],
        dropdowns: [
            {
                name: 'Gestionar',
                role: 10,
                active: false,
                icon: 'mdi-cog-box',
                items: [
                    {
                        name: 'Periodos de evaluación',
                        href: route('assessmentPeriods.index.view'),
                        role: 10,
                        icon: 'mdi-account-cog'
                    },
                    {
                        name: 'Dependencias',
                        href: route('dependencies.index.view'),
                        role: 10,
                        icon: 'mdi-account-cog'
                    },
                    {
                        name: 'Funcionarios',
                        href: route('functionaries.index.view'),
                        role: 10,
                        icon: 'mdi-account-cog'
                    },
                    {
                        name: 'Estado de la evaluación',
                        href: route('api.dependencies.all.assessmentStatus.view'),
                        role: 10,
                        icon: 'mdi-account-cog'
                    },
                    {
                        name: 'Posiciones',
                        href: route('positions.index.view'),
                        role: 10,
                        icon: 'mdi-account-cog'
                    },
                    {
                        name: 'Asignación de Cargos a Posiciones',
                        href: route('positions.assignment.index.view'),
                        role: 10,
                        icon: 'mdi-account-cog'
                    },
                    {
                        name: 'Clientes Externos',
                        href: route('externalClients.index.view'),
                        role: 10,
                        icon: 'mdi-account-cog'
                    },
                    {
                        name: 'Ideales de respuesta',
                        href: route('responseIdeals.index.view'),
                        role: 10,
                        icon: 'mdi-account-cog'
                    },
                    {
                        name: 'Competencias',
                        href: route('competences.index.view'),
                        role: 10,
                        icon: 'mdi-account-cog'
                    },
                    {
                        name: 'Formularios',
                        href: route('forms.index.view'),
                        role: 10,
                        icon: 'mdi-account-cog'
                    },
                    {
                        name: 'Compromisos',
                        href: route('commitments.landing'),
                        role: 10,
                        icon: 'mdi-account-cog'
                    },
                    {
                        name: 'Roles',
                        href: route('roles.index'),
                        role: 10,
                        icon: 'mdi-cog-box'
                    },
                    {
                        name: 'Usuarios',
                        href: route('users.index'),
                        role: 10,
                        icon: 'mdi-account-cog'
                    },
                ]
            },

            {
                name: 'Gestionar',
                role: 3,
                active: false,
                icon: 'mdi-cog-box',
                items: [
                    {
                        name: 'Mis evaluaciones',
                        href: route('tests.index.view'),
                        role: 3,
                        icon: 'mdi-cog-box'
                    },
                    {
                        name: 'Compromisos',
                        href: route('commitments.landing'),
                        role: 3,
                        icon: 'mdi-account-cog'
                    },
                ]
            },

            {
                name: 'Gestionar',
                role: 4,
                active: false,
                icon: 'mdi-cog-box',
                items: [
                    {
                        name: 'Dependencias asignadas',
                        href: route('redirect'),
                        role: 4,
                        icon: 'mdi-cog-box'
                    },
                ]
            },


            {
                name: 'Generar',
                role: 10,
                active: false,
                icon: 'mdi-cog-box',
                items: [
                    {
                        name: 'Reportes por evaluación',
                        href: route('reports.assessments.index'),
                        role: 10,
                        icon: 'mdi-calendar'
                    },
                ]
            },
        ],
        group: null,
        initials: '',
    }),

    props: {
        token: String
    },

    methods: {

        logout() {
            this.$refs.logoutform.submit();
        },

        // logout() {
        //     this.$inertia.post(route('logout'));
        // },

        triggerFunction(functionName) {
            this[functionName]();
        },
    },

    async created() {
        //Get the inicials

        console.log(this.$page.props.user,'the user');
        let name = this.$page.props.user.name;
        let splitName = "";

        if (name.trim().indexOf(' ') !== -1){
            splitName = name.split(' ');
        }

        else{
            splitName = name;
        }

        this.initials = `${splitName[0].charAt(0)}${splitName[1].charAt(0)}`;

    }

}
</script>
<style>

.active-button {
    color: #FAFAFA !important;
}

.normal-button {
    color: #BDBDBD !important;
}

</style>
