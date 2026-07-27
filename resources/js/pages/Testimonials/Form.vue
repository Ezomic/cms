<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';

interface Data { id: number; quote: string; quote_nl: string | null; author_name: string | null; author_role: string | null; company_name: string | null; featured: boolean }
const props = defineProps<{ testimonial: Data | null }>();
const isEdit = computed(() => props.testimonial !== null);
const lang = ref<'en' | 'nl'>('en');

const form = useForm({
    quote: props.testimonial?.quote ?? '',
    quote_nl: props.testimonial?.quote_nl ?? '',
    author_name: props.testimonial?.author_name ?? '',
    author_role: props.testimonial?.author_role ?? '',
    company_name: props.testimonial?.company_name ?? '',
    featured: props.testimonial?.featured ?? false,
});

const submit = () => isEdit.value
    ? form.put(`/admin/testimonials/${props.testimonial!.id}`)
    : form.post('/admin/testimonials');
</script>

<template>
    <Head :title="isEdit ? 'Edit testimonial' : 'New testimonial'" />
    <AdminLayout :title="isEdit ? 'Edit testimonial' : 'New testimonial'">
        <template #actions>
            <Button as="a" href="/admin/testimonials" variant="ghost">Cancel</Button>
            <Button :disabled="form.processing" @click="submit">{{ isEdit ? 'Save changes' : 'Create' }}</Button>
        </template>

        <form class="max-w-2xl" @submit.prevent="submit">
            <Card>
                <CardHeader>
                    <CardTitle>Testimonial</CardTitle>
                    <div class="flex gap-1">
                        <button type="button" class="lt" :class="lang === 'en' ? 'lt-on' : ''" @click="lang = 'en'">EN</button>
                        <button type="button" class="lt" :class="lang === 'nl' ? 'lt-on' : ''" @click="lang = 'nl'">NL</button>
                    </div>
                </CardHeader>
                <CardContent class="flex flex-col gap-4">
                    <div v-if="lang === 'en'">
                        <Label for="quote">Quote</Label>
                        <Textarea id="quote" v-model="form.quote" :rows="4" />
                        <p v-if="form.errors.quote" class="mt-1 text-xs text-destructive">{{ form.errors.quote }}</p>
                    </div>
                    <div v-else>
                        <Label for="quote_nl">Quote (NL)</Label>
                        <Textarea id="quote_nl" v-model="form.quote_nl" :rows="4" />
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div><Label for="an">Author name</Label><Input id="an" v-model="form.author_name" /></div>
                        <div><Label for="ar">Author role</Label><Input id="ar" v-model="form.author_role" /></div>
                    </div>
                    <div><Label for="co">Company</Label><Input id="co" v-model="form.company_name" /></div>
                    <div>
                        <Label>Featured on home page</Label>
                        <div class="flex items-center gap-2.5"><Switch v-model="form.featured" /><span class="text-sm">{{ form.featured ? 'Featured' : 'Not featured' }}</span></div>
                    </div>
                </CardContent>
            </Card>
        </form>
    </AdminLayout>
</template>
