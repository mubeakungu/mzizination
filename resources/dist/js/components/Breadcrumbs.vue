<template>
    <nav class="breadcrumbs" v-if="items.length > 0">
        <ol class="breadcrumbs-list">
            <li v-for="(item, index) in items" :key="index" class="breadcrumbs-item">
                <router-link 
                    v-if="index < items.length - 1 && item.to"
                    :to="item.to"
                    class="breadcrumbs-link"
                >
                    {{ item.label }}
                </router-link>
                <span v-else class="breadcrumbs-current">
                    {{ item.label }}
                </span>
                <span v-if="index < items.length - 1" class="breadcrumbs-separator">/</span>
            </li>
        </ol>
    </nav>
</template>

<script>
export default {
    name: 'Breadcrumbs',
    props: {
        items: {
            type: Array,
            required: true,
            default: () => []
        }
    }
}
</script>

<style scoped>
.breadcrumbs {
    margin-bottom: 16px;
    padding: 8px 0;
}
.breadcrumbs-list {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    list-style: none;
    margin: 0;
    padding: 0;
    font-size: 14px;
}
.breadcrumbs-item {
    display: flex;
    align-items: center;
}
.breadcrumbs-link {
    color: #2155ed;
    text-decoration: none;
    transition: color 0.2s ease;
}
.breadcrumbs-link:hover {
    color: #1a44c4;
    text-decoration: underline;
}
.breadcrumbs-current {
    color: #2e3956;
    font-weight: 500;
}
.breadcrumbs-separator {
    margin: 0 8px;
    color: #999;
}
@media screen and (max-width: 768px) {
    .breadcrumbs {
        font-size: 12px;
    }
    .breadcrumbs-separator {
        margin: 0 4px;
    }
}
</style>

