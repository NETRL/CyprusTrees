<template>
    <Modal v-if="showModal" class="m-4">
        <template #body>
            <div
                class="no-scrollbar relative w-full max-w-[700px] overflow-y-auto rounded-3xl bg-white dark:bg-gray-900 p-5">
                <!-- Close Button -->
                <button @click="() => emit('closeModal')"
                    class="transition-color absolute right-5 top-5 z-999 flex h-11 w-11 items-center justify-center rounded-full bg-gray-100 text-gray-400 hover:bg-gray-200 hover:text-gray-600 dark:bg-gray-700 dark:bg-white/[0.05] dark:text-gray-400 dark:hover:bg-white/[0.07] dark:hover:text-gray-300">
                    <svg class="fill-current" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd"
                            d="M6.04289 16.5418C5.65237 16.9323 5.65237 17.5655 6.04289 17.956C6.43342 18.3465 7.06658 18.3465 7.45711 17.956L11.9987 13.4144L16.5408 17.9565C16.9313 18.347 17.5645 18.347 17.955 17.9565C18.3455 17.566 18.3455 16.9328 17.955 16.5423L13.4129 12.0002L17.955 7.45808C18.3455 7.06756 18.3455 6.43439 17.955 6.04387C17.5645 5.65335 16.9313 5.65335 16.5408 6.04387L11.9987 10.586L7.45711 6.04439C7.06658 5.65386 6.43342 5.65386 6.04289 6.04439C5.65237 6.43491 5.65237 7.06808 6.04289 7.4586L10.5845 12.0002L6.04289 16.5418Z"
                            fill="" />
                    </svg>
                </button>

                <!-- Not Authenticated State -->
                <template v-if="!isAuthenticated">
                    <div class="px-2 pr-14">
                        <h4
                            class="mb-2 text-2xl font-semibold text-gray-800 dark:text-white/90 flex items-center gap-3">
                            <Flag class="w-7 h-7 text-red-600" />
                            Report an Issue
                        </h4>
                        <p class="mb-6 text-sm text-gray-500 dark:text-gray-400 lg:mb-7">
                            Please log in to report an issue with this tree
                        </p>
                    </div>

                    <div
                        class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg p-4 mb-6">
                        <div class="flex items-start gap-3">
                            <AlertCircle class="w-5 h-5 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5" />
                            <div class="text-sm text-amber-700 dark:text-amber-400">
                                Authentication required to submit reports. This helps us track and respond to issues
                                effectively.
                            </div>
                        </div>
                    </div>

                    <Link :href="route('login')"
                        class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-lg bg-gradient-to-r from-emerald-600 to-teal-600 text-white font-semibold hover:from-emerald-700 hover:to-teal-700 transition-all">
                    <LogIn class="w-4 h-4" />
                    Login to Continue
                    </Link>
                </template>

                <!-- Authenticated State -->
                <template v-else>
                    <div class="px-2 pr-14">
                        <h4
                            class="mb-2 text-2xl font-semibold text-gray-800 dark:text-white/90 flex items-center gap-3">
                            <Flag class="w-7 h-7 text-red-600" />
                            Report an Issue
                        </h4>
                        <p class="mb-6 text-sm text-gray-500 dark:text-gray-400 lg:mb-7">
                            Please fill out the form to report an issue with this tree
                        </p>
                    </div>

                    <!-- Tree Information Summary -->
                    <div
                        class="bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-lg p-4 mb-6 mx-2">
                        <div class="flex items-start gap-3">
                            <TreeDeciduous class="w-5 h-5 text-emerald-600 dark:text-emerald-400 shrink-0 mt-0.5" />
                            <div class="flex-1">
                                <div class="text-sm font-semibold text-emerald-900 dark:text-emerald-300 mb-1">
                                    {{ treeSpecies?.common_name || 'Unknown Tree' }}
                                </div>
                                <div class="text-xs text-emerald-700 dark:text-emerald-400">
                                    ID: {{ props.tree?.id }} • {{ props.tree?.address || 'Location not specified' }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="custom-scrollbar max-h-[500px] overflow-y-auto p-2">
                        <form @submit.prevent="handleSubmit" class="flex flex-col space-y-6">

                            <!-- Report Type -->
                            <FormField component="Dropdown" v-model="form.report_type_id" :displayErrors="true"
                                :required="true" label="Issue Type" name="report_type_id" :options="reportTypeOptions"
                                optionLabel="label" optionValue="value" />

                            <!-- Description -->
                            <div>
                                <label class="mb-2 block text-sm font-semibold text-gray-700 dark:text-gray-300">
                                    Description
                                </label>
                                <textarea v-model="form.description" rows="4"
                                    placeholder="Please describe the issue in detail..."
                                    class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-red-500 focus:border-transparent resize-none transition-colors"></textarea>
                                <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">
                                    Optional: Provide additional details about the issue
                                </p>
                                <p v-if="form.errors.description" class="mt-1.5 text-xs text-red-600 dark:text-red-400">
                                    {{ form.errors.description }}
                                </p>
                            </div>

                            <!-- Photo Upload (Optional) -->
                            <div>
                                <label class="mb-2 block text-sm font-semibold text-gray-700 dark:text-gray-300">
                                    Photo Evidence (Optional)
                                </label>
                                <div
                                    class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-6 text-center hover:border-red-500 dark:hover:border-red-500 transition-colors">
                                    <!-- Hidden file inputs -->
                                    <input 
                                        type="file" 
                                        @change="handleFileUpload" 
                                        accept="image/*"
                                        class="hidden"
                                        ref="fileInput" 
                                    />
                                    <input 
                                        type="file" 
                                        @change="handleFileUpload" 
                                        accept="image/*"
                                        capture="environment"
                                        class="hidden"
                                        ref="cameraInput" 
                                    />

                                    <!-- Upload Preview -->
                                    <div v-if="photoPreview" class="relative inline-block">
                                        <img :src="photoPreview" class="max-h-40 rounded-lg" />
                                        <button type="button" @click="removePhoto"
                                            class="absolute -top-2 -right-2 bg-red-600 text-white rounded-full p-1.5 hover:bg-red-700 transition-colors shadow-lg">
                                            <X class="w-4 h-4" />
                                        </button>
                                    </div>

                                    <!-- Upload Options -->
                                    <div v-else>
                                        <Camera class="w-12 h-12 mx-auto text-gray-400 mb-3" />
                                        
                                        <!-- Mobile: Show camera button -->
                                        <div v-if="isMobile" class="space-y-2">
                                            <button
                                                type="button"
                                                @click="$refs.cameraInput.click()"
                                                class="w-full px-4 py-3 rounded-lg bg-red-600 text-white font-medium hover:bg-red-700 transition-colors flex items-center justify-center gap-2"
                                            >
                                                <Camera class="w-4 h-4" />
                                                Take Photo
                                            </button>
                                            <button
                                                type="button"
                                                @click="$refs.fileInput.click()"
                                                class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 font-medium hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors flex items-center justify-center gap-2"
                                            >
                                                <ImageIcon class="w-4 h-4" />
                                                Choose from Gallery
                                            </button>
                                        </div>
                                        
                                        <!-- Desktop: Show single upload button -->
                                        <div v-else>
                                            <button
                                                type="button"
                                                @click="$refs.fileInput.click()"
                                                class="text-sm text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 font-medium"
                                            >
                                                Click to upload a photo
                                            </button>
                                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                JPG, PNG up to 15MB
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <p v-if="form.errors.photo" class="mt-1.5 text-xs text-red-600 dark:text-red-400">
                                    {{ form.errors.photo }}
                                </p>
                            </div>

                            <!-- Contact Information Notice -->
                            <div
                                class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                                <div class="flex items-start gap-3">
                                    <Info class="w-5 h-5 text-blue-600 dark:text-blue-400 shrink-0 mt-0.5" />
                                    <div class="text-xs text-blue-700 dark:text-blue-400">
                                        Your report will be submitted as <strong>{{ user?.name || user?.email
                                        }}</strong>.
                                        Our team will review your report.
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex gap-3 pt-2">
                                <button type="button" @click="() => emit('closeModal')"
                                    class="flex-1 px-6 py-3 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 font-semibold hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors"
                                    :disabled="form.processing">
                                    Cancel
                                </button>
                                <button type="submit"
                                    class="flex-1 px-6 py-3 rounded-lg bg-gradient-to-r from-red-600 to-orange-600 text-white font-semibold hover:from-red-700 hover:to-orange-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all flex items-center justify-center gap-2"
                                    :disabled="form.processing || !form.report_type_id">
                                    <Loader2 v-if="form.processing" class="w-4 h-4 animate-spin" />
                                    <Flag v-else class="w-4 h-4" />
                                    {{ form.processing ? 'Submitting...' : 'Submit Report' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </template>

            </div>
        </template>
    </Modal>
</template>

<script setup>
import { ref, computed, inject, onMounted, onUnmounted } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import { useAuth } from '@/Composables/useAuth';
import { Flag, AlertCircle, LogIn, TreeDeciduous, Camera, X, Info, Loader2, Image as ImageIcon } from 'lucide-vue-next';
import FormField from '@/Components/Primitives/FormField.vue';

const emit = defineEmits(['closeModal', 'submitted']);

const props = defineProps({
    showModal: {
        type: Boolean,
        default: false,
    },
    tree: {
        type: Object,
        default: null,
    },
});

const reportTypes = inject('reportTypes');

const reportTypeOptions = computed(() =>
    reportTypes.map(index => ({
        label: `${index.name}`,
        value: index.id,
    }))
);

const { user, isAuthenticated } = useAuth();

// File input refs
const fileInput = ref(null);
const cameraInput = ref(null);
const photoPreview = ref(null);

// Detect if mobile device
const isMobile = ref(false);

onMounted(() => {
    // Simple mobile detection
    isMobile.value = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) 
        || window.innerWidth < 768;
    
    // Add resize listener for responsive detection
    window.addEventListener('resize', handleResize);
});

onUnmounted(() => {
    window.removeEventListener('resize', handleResize);
});

const handleResize = () => {
    isMobile.value = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) 
        || window.innerWidth < 768;
};

// Parse tree species data
const treeSpecies = computed(() => {
    if (!props.tree?.species) return null;
    try {
        return typeof props.tree.species === 'string'
            ? JSON.parse(props.tree.species)
            : props.tree.species;
    } catch {
        return null;
    }
});

// Form setup
const form = useForm({
    report_type_id: null,
    // created_by: user.value.id,
    created_at: new Date(),
    tree_id: computed(() => props.tree?.id),
    lat: computed(() => props.tree?.lat),
    lon: computed(() => props.tree?.lon),
    description: '',
    photo: null,
    resolved_at: null,
    source: null,
});

// Handle file upload
const handleFileUpload = (event) => {
    const file = event.target.files[0];
    if (!file) return;

    if(event.target.attributes.capture){
        form.source = 'camera'
    }else{
        form.source = 'upload'
    }

    // Validate file size
    if (file.size > 15 * 1024 * 1024) {
        alert('File size must be less than 15MB');
        return;
    }

    // Validate file type
    if (!file.type.startsWith('image/')) {
        alert('Please upload an image file');
        return;
    }

    form.photo = file;

    // Create preview
    const reader = new FileReader();
    reader.onload = (e) => {
        photoPreview.value = e.target.result;
    };
    reader.readAsDataURL(file);
};

// Remove photo
const removePhoto = () => {
    form.photo = null;
    photoPreview.value = null;
    if (fileInput.value) {
        fileInput.value.value = '';
    }
    if (cameraInput.value) {
        cameraInput.value.value = '';
    }
};

// Handle form submission
const handleSubmit = () => {
    form.post(route('citizenReports.store'), {
        preserveScroll: true,
        onSuccess: () => {
            // Reset form
            form.reset();
            photoPreview.value = null;
            // Close modal
            emit('submitted');
        },
        onError: (errors) => {
            console.error('Form submission errors:', errors);
        }
    });
};
</script>
