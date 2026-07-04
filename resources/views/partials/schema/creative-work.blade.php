@php
$creativeWorkSchema = array_filter([
    '@context'     => 'https://schema.org',
    '@type'        => 'CreativeWork',
    'name'         => $project->name,
    'description'  => $project->metaDescription(),
    'url'          => route('project.show', $project->slug),
    'image'        => route('og.project', $project->slug),
    'dateModified' => $project->updated_at?->toAtomString(),
    'keywords'     => implode(', ', $project->tagList()),
    'author'       => ['@type' => 'Person', 'name' => $profile->name],
]);
@endphp
<script type="application/ld+json">{!! json_encode($creativeWorkSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}</script>
