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
    category: string;
}
const props = defineProps<{ skill: Data | null }>();
const isEdit = computed(() => props.skill !== null);

const form = useForm({
    category: props.skill?.category ?? '',
    name: props.skill?.name ?? '',
});

const submit = () => (isEdit.value ? form.put(`/admin/skills/${props.skill!.id}`) : form.post('/admin/skills'));
</script>

<template>
    <Head :title="isEdit ? 'Edit skill' : 'New skill'" />
    <AdminLayout :title="isEdit ? 'Edit skill' : 'New skill'">
        <template #actions>
            <Button as="a" href="/admin/skills" variant="ghost">Cancel</Button>
            <Button :disabled="form.processing" @click="submit">{{ isEdit ? 'Save changes' : 'Add skill' }}</Button>
        </template>

        <form class="max-w-md" @submit.prevent="submit">
            <Card>
                <CardContent class="flex flex-col gap-4">
                    <div>
                        <Label for="name">Name</Label>
                        <Input id="name" v-model="form.name" placeholder="Laravel" />
                        <p v-if="form.errors.name" class="text-destructive mt-1 text-xs">{{ form.errors.name }}</p>
                    </div>
                    <div>
                        <Label for="category">Category</Label>
                        <Input id="category" v-model="form.category" placeholder="Backend" />
                        <p v-if="form.errors.category" class="text-destructive mt-1 text-xs">{{ form.errors.category }}</p>
                        <p class="text-muted-foreground mt-1 text-xs">Skills are grouped by category on the site.</p>
                    </div>
                </CardContent>
            </Card>
        </form>
    </AdminLayout>
</template>
