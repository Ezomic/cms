const ENTITIES: Record<string, string> = {
    '&laquo;': '«',
    '&raquo;': '»',
    '&amp;': '&',
};

/**
 * Laravel's paginator ships its previous/next labels as HTML entities, which is
 * why these links used v-html. Decoding the handful of entities it emits lets
 * them render as plain text instead.
 */
export const paginationLabel = (label: string): string => label.replace(/&laquo;|&raquo;|&amp;/g, (entity) => ENTITIES[entity] ?? entity);
