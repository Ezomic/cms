<script setup lang="ts">
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';

interface Profile { [key: string]: string | boolean | null }
const props = defineProps<{ profile: Profile }>();
const p = props.profile;
const lang = ref<'en' | 'nl'>('en');

const form = useForm({
    name: (p.name as string) ?? '',
    city: (p.city as string) ?? '',
    tagline: (p.tagline as string) ?? '',
    hero_headline: (p.hero_headline as string) ?? '',
    hero_subtext: (p.hero_subtext as string) ?? '',
    available: (p.available as boolean) ?? false,
    email: (p.email as string) ?? '',
    linkedin_url: (p.linkedin_url as string) ?? '',
    github_url: (p.github_url as string) ?? '',
    rate: (p.rate as string) ?? '',
    availability_from: (p.availability_from as string) ?? '',
    kvk_number: (p.kvk_number as string) ?? '',
    meta_title: (p.meta_title as string) ?? '',
    meta_description: (p.meta_description as string) ?? '',
    docs_intro: (p.docs_intro as string) ?? '',
    tagline_nl: (p.tagline_nl as string) ?? '',
    hero_headline_nl: (p.hero_headline_nl as string) ?? '',
    hero_subtext_nl: (p.hero_subtext_nl as string) ?? '',
    docs_intro_nl: (p.docs_intro_nl as string) ?? '',
    meta_title_nl: (p.meta_title_nl as string) ?? '',
    meta_description_nl: (p.meta_description_nl as string) ?? '',
});

const submit = () => form.put('/admin/profile', { preserveScroll: true });
</script>

<template>
    <Head title="Profile" />
    <AdminLayout title="Profile">
        <template #actions><Button :disabled="form.processing" @click="submit">Save changes</Button></template>

        <form class="grid grid-cols-1 items-start gap-5 lg:grid-cols-[1fr_320px]" @submit.prevent="submit">
            <div class="flex flex-col gap-5">
                <Card>
                    <CardHeader>
                        <CardTitle>Identity</CardTitle>
                        <div class="flex gap-1">
                            <button type="button" class="lt" :class="lang === 'en' ? 'lt-on' : ''" @click="lang = 'en'">EN</button>
                            <button type="button" class="lt" :class="lang === 'nl' ? 'lt-on' : ''" @click="lang = 'nl'">NL</button>
                        </div>
                    </CardHeader>
                    <CardContent class="flex flex-col gap-4">
                        <div class="grid grid-cols-2 gap-3">
                            <div><Label for="name">Name</Label><Input id="name" v-model="form.name" /><p v-if="form.errors.name" class="mt-1 text-xs text-destructive">{{ form.errors.name }}</p></div>
                            <div><Label for="city">City</Label><Input id="city" v-model="form.city" /></div>
                        </div>
                        <template v-if="lang === 'en'">
                            <div><Label for="tagline">Tagline</Label><Input id="tagline" v-model="form.tagline" /></div>
                            <div><Label for="hh">Hero headline</Label><Input id="hh" v-model="form.hero_headline" /></div>
                            <div><Label for="hs">Hero subtext</Label><Textarea id="hs" v-model="form.hero_subtext" :rows="3" /></div>
                            <div><Label for="di">Docs intro</Label><Textarea id="di" v-model="form.docs_intro" :rows="3" /></div>
                        </template>
                        <template v-else>
                            <div><Label for="tagline_nl">Tagline (NL)</Label><Input id="tagline_nl" v-model="form.tagline_nl" /></div>
                            <div><Label for="hh_nl">Hero headline (NL)</Label><Input id="hh_nl" v-model="form.hero_headline_nl" /></div>
                            <div><Label for="hs_nl">Hero subtext (NL)</Label><Textarea id="hs_nl" v-model="form.hero_subtext_nl" :rows="3" /></div>
                            <div><Label for="di_nl">Docs intro (NL)</Label><Textarea id="di_nl" v-model="form.docs_intro_nl" :rows="3" /></div>
                        </template>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>SEO</CardTitle>
                        <div class="flex gap-1">
                            <button type="button" class="lt" :class="lang === 'en' ? 'lt-on' : ''" @click="lang = 'en'">EN</button>
                            <button type="button" class="lt" :class="lang === 'nl' ? 'lt-on' : ''" @click="lang = 'nl'">NL</button>
                        </div>
                    </CardHeader>
                    <CardContent class="flex flex-col gap-4">
                        <template v-if="lang === 'en'">
                            <div><Label for="mt">Meta title</Label><Input id="mt" v-model="form.meta_title" /></div>
                            <div><Label for="md">Meta description</Label><Textarea id="md" v-model="form.meta_description" :rows="3" /></div>
                        </template>
                        <template v-else>
                            <div><Label for="mt_nl">Meta title (NL)</Label><Input id="mt_nl" v-model="form.meta_title_nl" /></div>
                            <div><Label for="md_nl">Meta description (NL)</Label><Textarea id="md_nl" v-model="form.meta_description_nl" :rows="3" /></div>
                        </template>
                    </CardContent>
                </Card>
            </div>

            <div class="flex flex-col gap-5">
                <Card>
                    <CardHeader><CardTitle>Availability &amp; rate</CardTitle></CardHeader>
                    <CardContent class="flex flex-col gap-4">
                        <div>
                            <Label>Available for work</Label>
                            <div class="flex items-center gap-2.5"><Switch v-model="form.available" /><span class="text-sm">{{ form.available ? 'Open for work' : 'Booked' }}</span></div>
                        </div>
                        <div><Label for="af">Available from</Label><Input id="af" v-model="form.availability_from" placeholder="Q3 2026" /></div>
                        <div>
                            <Label for="rate">Rate (internal)</Label>
                            <Input id="rate" v-model="form.rate" />
                            <p class="mt-1 text-xs text-muted-foreground">Not shown publicly — the site shows “On request”.</p>
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader><CardTitle>Links</CardTitle></CardHeader>
                    <CardContent class="flex flex-col gap-4">
                        <div><Label for="email">Email</Label><Input id="email" v-model="form.email" class="font-mono" /><p v-if="form.errors.email" class="mt-1 text-xs text-destructive">{{ form.errors.email }}</p></div>
                        <div><Label for="li">LinkedIn URL</Label><Input id="li" v-model="form.linkedin_url" class="font-mono" /></div>
                        <div><Label for="gh">GitHub URL</Label><Input id="gh" v-model="form.github_url" class="font-mono" /></div>
                        <div><Label for="kvk">KVK number</Label><Input id="kvk" v-model="form.kvk_number" class="font-mono" /></div>
                    </CardContent>
                </Card>
            </div>
        </form>
    </AdminLayout>
</template>
