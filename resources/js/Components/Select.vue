<script setup>
const model = defineModel({
    type: null,
    required: true,
});

defineProps({
    label: {
        type: String,
        required: true,
    },
    options: {
        type: Array,
        default: [],
    },
    is_required: {
        type: Boolean,
        default: false,
    },
    message: Array,
});
</script>

<template>
    <div class="mb-4">
        <label class="block text-gray-700 text-sm font-bold mb-2">
            {{ label }}
            <span v-if="is_required" class="text-red-500 ml-1">*</span>
        </label>
        <select v-model="model" :required="is_required" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring focus:ring-indigo-300">
          <option :value="option.value" :selected="option.selected" v-for="option in options">{{ option.name}}</option>
        </select>

        <small class="text-red-500" v-if="message">
            {{ message ? message[0] : "" }}
        </small>
    </div>
</template>