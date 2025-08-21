<script setup lang="ts">
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({ name: '', email: '', password: '', password_confirmation: '' });

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Register" />

        <div class="min-h-screen flex flex-col items-center justify-center bg-gray-50 px-4 py-10">
            <!-- HEADER CUSTOM -->
            <header class="flex flex-col items-center gap-4 mb-8">
                <svg class="h-16 w-16 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                     viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <h1 class="text-3xl font-bold">🔎 Classificação de Logs DNS</h1>
                <p class="text-center max-w-lg text-gray-500">
                    Plataforma para análise de consultas DNS com auxílio de Inteligência Artificial.<br>
                    Classifique domínios como <span class="text-green-600">Seguro</span>,
                    <span class="text-yellow-500">Suspeito</span> ou
                    <span class="text-red-600">Malicioso</span>.
                </p>
            </header>

            <div class="w-full max-w-md bg-white shadow-lg rounded-2xl p-8 border border-gray-200">
                <h2 class="text-xl font-semibold text-gray-800 text-center mb-4">Criar Conta</h2>

                <form @submit.prevent="submit" class="space-y-5">
                    <div>
                        <InputLabel for="name" value="Nome" class="text-gray-700 font-medium" />
                        <TextInput
                            id="name" type="text"
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            v-model="form.name" required autofocus autocomplete="name"
                        />
                        <InputError class="mt-2" :message="form.errors.name" />
                    </div>

                    <div>
                        <InputLabel for="email" value="Email" class="text-gray-700 font-medium" />
                        <TextInput
                            id="email" type="email"
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            v-model="form.email" required autocomplete="username"
                        />
                        <InputError class="mt-2" :message="form.errors.email" />
                    </div>

                    <div>
                        <InputLabel for="password" value="Senha" class="text-gray-700 font-medium" />
                        <TextInput
                            id="password" type="password"
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            v-model="form.password" required autocomplete="new-password"
                        />
                        <InputError class="mt-2" :message="form.errors.password" />
                    </div>

                    <div>
                        <InputLabel for="password_confirmation" value="Confirmar Senha" class="text-gray-700 font-medium" />
                        <TextInput
                            id="password_confirmation" type="password"
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            v-model="form.password_confirmation" required autocomplete="new-password"
                        />
                        <InputError class="mt-2" :message="form.errors.password_confirmation" />
                    </div>

                    <div class="flex items-center justify-between pt-2">
                        <Link
                            :href="route('login')"
                            class="text-sm text-indigo-600 hover:text-indigo-800 font-medium"
                        >Já tem conta?</Link>

                        <PrimaryButton
                            class="py-2.5 px-6 text-base font-medium rounded-lg shadow-md bg-indigo-600 hover:bg-indigo-700 text-white transition"
                            :class="{ 'opacity-25': form.processing }"
                            :disabled="form.processing"
                        >
                            Registrar
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </GuestLayout>
</template>
