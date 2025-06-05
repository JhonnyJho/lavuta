<script setup>

import { useForm } from '@inertiajs/vue3';
import TextInput from '../Components/TextInput.vue';

const form = useForm({
    email: null,
    password: null,
    remember: null
});

const submit =() => {
    form.post(route("login"), {
        onError: () => form.reset("password")
    });
};

</script>

<template>
    <Head title="Login"/>

    <h1 class="title">Login into your account</h1>

    <div class="w-2/4 mx-auto">

        <form @submit.prevent="submit">

            <TextInput v-model="form.email" type="email" name="email" :message="form.errors.email" />

            <TextInput v-model="form.password" type="password" name="password" :message="form.errors.password" />

            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-2">
                    <input v-model="form.remember" type="checkbox">
                    <label>Remember me</label>
                </div>

                <p class="text-slate-600">Don't have an account?<a :href="route('register')" class="text-link"> Register</a></p>
                
            </div>

            <div>
                <button class="primary-btn" :disabled="form.processing">Login</button>
            </div>

        </form>

    </div>
</template>