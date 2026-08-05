<script setup lang="ts">
import { computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

interface Data {
    id: number;
    name: string;
    email: string;
}
const props = defineProps<{ user: Data | null }>();
const isEdit = computed(() => props.user !== null);

const form = useForm({
    name: props.user?.name ?? '',
    email: props.user?.email ?? '',
});

const submit = () => (isEdit.value ? form.put(`/admin/users/${props.user!.id}`) : form.post('/admin/users'));
</script>

<template>
    <Head :title="isEdit ? 'Edit user' : 'Add user'" />
    <AdminLayout :title="isEdit ? 'Edit user' : 'Add user'">
        <template #actions>
            <Button as="a" href="/admin/users" variant="ghost">Cancel</Button>
            <Button :disabled="form.processing" @click="submit">{{ isEdit ? 'Save changes' : 'Add user' }}</Button>
        </template>

        <form class="max-w-md" @submit.prevent="submit">
            <Card>
                <CardContent class="flex flex-col gap-4">
                    <div>
                        <Label for="name">Name</Label>
                        <Input id="name" v-model="form.name" />
                        <p v-if="form.errors.name" class="text-destructive mt-1 text-xs">{{ form.errors.name }}</p>
                    </div>
                    <div>
                        <Label for="email">Email</Label>
                        <Input id="email" v-model="form.email" type="email" />
                        <p v-if="form.errors.email" class="text-destructive mt-1 text-xs">{{ form.errors.email }}</p>
                    </div>
                    <p class="text-muted-foreground text-xs">
                        No password: new admins sign in with an emailed one-time code, then register a passkey.
                    </p>
                </CardContent>
            </Card>
        </form>
    </AdminLayout>
</template>
