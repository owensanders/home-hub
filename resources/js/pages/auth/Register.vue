<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import AuthBase from '@/layouts/AuthLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';

const props = defineProps<{
    email?: string;
}>();

const form = useForm({
    name: '',
    email: props.email ?? '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => {
            form.reset('password', 'password_confirmation');
        },
    });
};
</script>

<template>
    <AuthBase title="Create an account" description="Set yourself up and start organising the house.">
        <Head title="Register" />

        <form class="flex flex-col gap-4" @submit.prevent="submit">
            <div class="flex flex-col gap-1.5">
                <label for="name" class="hh-label">Name</label>
                <input
                    id="name"
                    v-model="form.name"
                    type="text"
                    class="hh-input"
                    required
                    autofocus
                    tabindex="1"
                    autocomplete="name"
                    placeholder="Full name"
                />
                <InputError :message="form.errors.name" />
            </div>

            <div class="flex flex-col gap-1.5">
                <label for="email" class="hh-label">Email address</label>
                <input
                    id="email"
                    v-model="form.email"
                    type="email"
                    class="hh-input"
                    required
                    tabindex="2"
                    autocomplete="email"
                    placeholder="you@example.com"
                />
                <InputError :message="form.errors.email" />
            </div>

            <div class="flex flex-col gap-1.5">
                <label for="password" class="hh-label">Password</label>
                <input
                    id="password"
                    v-model="form.password"
                    type="password"
                    class="hh-input"
                    required
                    tabindex="3"
                    autocomplete="new-password"
                    placeholder="••••••••"
                />
                <InputError :message="form.errors.password" />
            </div>

            <div class="flex flex-col gap-1.5">
                <label for="password_confirmation" class="hh-label">Confirm password</label>
                <input
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    class="hh-input"
                    required
                    tabindex="4"
                    autocomplete="new-password"
                    placeholder="••••••••"
                />
                <InputError :message="form.errors.password_confirmation" />
            </div>

            <button type="submit" class="hh-btn mt-2 bg-hh-coral text-white" tabindex="5" :disabled="form.processing">
                <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                Create account
            </button>
        </form>

        <p class="mt-6 text-center text-[13px] text-hh-ink3">
            Already have an account?
            <Link :href="route('login')" class="font-semibold text-hh-coral hover:opacity-75" :tabindex="6">Log in</Link>
        </p>
    </AuthBase>
</template>
