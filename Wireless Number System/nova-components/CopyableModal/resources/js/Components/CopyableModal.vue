<template>
    <div class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center" aria-labelledby="modal-title"
        role="dialog" aria-modal="true">
        <!-- Background overlay -->
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-black opacity-50 transition-opacity" aria-hidden="true"></div>

            <!-- Modal positioning -->
            <div
                class="relative inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle w-full md:max-w-xl">
                <!-- Modal content -->
                <div class="px-6 py-4">
                    <div v-if="title" class="text-lg font-semibold text-gray-900 dark:text-white mb-2">{{ title }}</div>
                    <div v-if="message" class="text-sm text-gray-600 dark:text-white mb-4">{{ message }}</div>

                    <div v-if="isTable" class="w-full overflow-x-auto overflow-y-auto pr-4"
                        style="max-height: calc(100vh - 20em);">
                        <table
                            class="min-w-full w-max mb-2 divide-y divide-gray-200 border border-gray-200 dark:border-gray-700 dark:bg-gray-800 rounded-lg">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th v-for="header in tableHeaders" :key="header"
                                        class="sticky top-0 z-10 bg-gray-50 dark:bg-gray-700 px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-white uppercase tracking-wider border-r border-gray-200 dark:border-gray-600 last:border-r-0">
                                        {{ header }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-100 dark:divide-gray-700">
                                <tr v-for="(row, idx) in tableRows" :key="idx"
                                    >
                                    <td v-for="header in tableHeaders" :key="header"
                                        class="px-4 py-2 font-mono text-sm text-gray-800 dark:text-gray-200 whitespace-pre-wrap break-all border-r border-gray-200 dark:border-gray-600 last:border-r-0">
                                        {{ row[header] || '' }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else-if="copyableItems"
                        class="bg-gray-50 border border-gray-200 dark:border-gray-700 dark:bg-gray-800 rounded-lg p-4">
                        <div class="font-semibold text-gray-700 dark:text-white mb-2">{{ label }}:</div>
                        <div class="font-mono text-sm text-gray-800 dark:text-gray-200 whitespace-pre-wrap break-all leading-relaxed overflow-y-auto"
                            style="max-height: calc(100vh - 20em);">
                            {{ copyableItems }}</div>
                    </div>

                    <!-- Copy Button -->
                    <div class="mt-6 flex flex-row justify-between md:justify-end space-x-3">
                        <button @click="closeModal"
                            class="inline-flex items-center px-4 py-2 bg-gray-300 rounded border border-transparent  font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 active:bg-gray-500 focus:outline-none focus:border-gray-500 focus:ring focus:ring-gray-300 transition ease-in-out duration-150">
                            Close
                        </button>
                        <button @click="copyValueToClipboard"
                            class="inline-flex items-center px-4 py-2 rounded bg-primary-500 border border-transparent font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-500 active:bg-primary-500 focus:outline-none focus:border-primary-500 focus:ring focus:ring-primary-500 disabled:opacity-25 transition ease-in-out duration-150"
                            :disabled="!copyableItems">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z">
                                </path>
                            </svg>
                            Copy to Clipboard
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'CopyableModal',

    props: {
        data: {
            type: Object,
            default: () => ({})
        }
    },

    data() {
        return {
            copyableItems: null,
            title: null,
            label: 'Value',
            message: null,
            isTable: false,
            tableHeaders: [],
            tableRows: [],
        };
    },

    async mounted() {
        await this.$nextTick();
        this.setupCopyableItems();
        this.disableBodyScroll();
    },

    beforeDestroy() {
        this.enableBodyScroll();
    },

    methods: {
        setupCopyableItems() {
            this.title = this.data.title || null;
            this.message = this.data.message || null;
            this.label = this.data.label || 'Value';
            let value = this.data.value;
            if (!value && this.$parent && this.$parent.value) {
                value = this.$parent.value;
            }
            if (value) {
                this.processValue(value);
            }
        },

        processValue(value) {

            // Handle object with numeric keys where index 0 is header
            if (Array.isArray(value) && value.length > 1) {
                const headerLine = value[0];
                const headers = headerLine.split(/\s{2,}/).map(h => h.trim());

                // Convert object to array of row objects
                const rows = [];
                for (let i = 1; i < Object.keys(value).length; i++) {
                    if (value[i]) {
                        const cols = value[i].split(/\s{2,}/);
                        const rowObj = {};
                        headers.forEach((h, j) => rowObj[h] = cols[j] || '');
                        rows.push(rowObj);
                    }
                }

                this.isTable = true;
                this.tableHeaders = headers;
                this.tableRows = rows;
                this.copyableItems = Object.values(value).join('\r\n');
            }
            else if (typeof value === 'string' && value.includes('\n') && value.includes('  ')) {
                // Try to parse string as table (columns separated by 2+ spaces)
                const lines = value.trim().split(/\r?\n/);
                if (lines.length > 1) {
                    const headerLine = lines[0];
                    const headers = headerLine.split(/\s{2,}/).map(h => h.trim());
                    const rows = lines.slice(1).map(line => {
                        const cols = line.split(/\s{2,}/);
                        const rowObj = {};
                        headers.forEach((h, i) => rowObj[h] = cols[i] || '');
                        return rowObj;
                    });
                    this.isTable = true;
                    this.tableHeaders = headers;
                    this.tableRows = rows;
                    this.copyableItems = value;
                } else {
                    this.isTable = false;
                    this.copyableItems = value;
                }
            } else {
                this.isTable = false;
                if (typeof value === 'object' && value !== null) {
                    this.copyableItems = Object.entries(value)
                        .map(([key, val]) => val)
                        .join('\r\n');
                } else {
                    this.copyableItems = String(value);
                }
            }
        },

        async copyValueToClipboard() {
            if (!this.copyableItems) {
                Nova.error('No content to copy');
                return;
            }

            try {
                // Method 1: Modern Clipboard API (Chrome 66+, Firefox 63+, Safari 13.1+)
                if (navigator.clipboard && window.isSecureContext) {
                    await navigator.clipboard.writeText(this.copyableItems);
                    Nova.success('Copied to clipboard successfully!');
                    this.closeModal();
                }
                // Method 2: IE/Edge clipboardData
                else if (window.clipboardData) {
                    window.clipboardData.setData('Text', this.copyableItems);
                    Nova.success('Copied to clipboard successfully!');
                    this.closeModal();
                }
                // Method 3: Fallback for older browsers
                else {
                    const textarea = document.createElement('textarea');
                    const [scrollTop, scrollLeft] = [
                        document.documentElement.scrollTop,
                        document.documentElement.scrollLeft,
                    ];
                    document.body.appendChild(textarea);
                    textarea.value = this.copyableItems;
                    textarea.focus();
                    textarea.select();
                    document.documentElement.scrollTop = scrollTop;
                    document.documentElement.scrollLeft = scrollLeft;
                    const successful = document.execCommand('copy');
                    textarea.remove();
                    if (successful) {
                        Nova.success('Copied to clipboard successfully!');
                        this.closeModal();
                    } else {
                        throw new Error('Copy command failed');
                    }
                }
            } catch (error) {
                console.error('Copy failed:', error);
                Nova.error('Failed to copy to clipboard. Please try selecting and copying manually.');
            }
        },

        disableBodyScroll() {
            // Store original body styles
            this.originalBodyStyle = document.body.style.overflow;
            // Prevent body scrolling
            document.body.style.overflow = 'hidden';
            // Add padding to prevent layout shift
            const scrollbarWidth = window.innerWidth - document.documentElement.clientWidth;
            if (scrollbarWidth > 0) {
                document.body.style.paddingRight = `${scrollbarWidth}px`;
            }
        },

        enableBodyScroll() {
            // Restore original body styles
            if (this.originalBodyStyle !== undefined) {
                document.body.style.overflow = this.originalBodyStyle;
                document.body.style.paddingRight = '';
            }
        },

        closeModal() {
            // Enable body scroll before closing
            this.enableBodyScroll();

            // Emit close event to parent component
            this.$emit('close');

            // Also try to close using Nova's modal system if available
            if (this.$parent && this.$parent.$emit) {
                this.$parent.$emit('close');
            }

            // Fallback: try to close any open modals
            const closeEvent = new CustomEvent('close-modal');
            window.dispatchEvent(closeEvent);
        },
    },
};
</script>

<style scoped>
/* Custom scrollbar styling */
.overflow-y-auto::-webkit-scrollbar {
    width: 8px;
}

.overflow-y-auto::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 4px;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
    transition: background 0.2s ease;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

/* Dark mode scrollbar */
.dark .overflow-y-auto::-webkit-scrollbar-track {
    background: #374151;
}

.dark .overflow-y-auto::-webkit-scrollbar-thumb {
    background: #6b7280;
}

.dark .overflow-y-auto::-webkit-scrollbar-thumb:hover {
    background: #9ca3af;
}

/* Firefox scrollbar */
.overflow-y-auto {
    scrollbar-width: thin;
    scrollbar-color: #cbd5e1 #f1f5f9;
}

.dark .overflow-y-auto {
    scrollbar-color: #6b7280 #374151;
}

/* Zebra striping for table rows */
tbody tr:nth-child(even) {
    background-color: #f9fafb;
}
.dark tbody tr:nth-child(even) {
    background-color: #374151;
}

/* Row hover effect */
tbody tr:hover {
    background-color: #e5e7eb !important;
    cursor: pointer;
}
.dark tbody tr:hover {
    background-color: #4b5563 !important;
}

/* Sticky header shadow */
thead th.sticky {
    box-shadow: 0 2px 6px -2px rgba(0,0,0,0.08);
    z-index: 20;
}

/* Responsive font and padding */
table th, table td {
    padding-left: 1rem;
    padding-right: 1rem;
    padding-top: 0.75rem;
    padding-bottom: 0.75rem;
    font-size: 0.95rem;
}
@media (max-width: 640px) {
    table th, table td {
        padding-left: 0.5rem;
        padding-right: 0.5rem;
        padding-top: 0.5rem;
        padding-bottom: 0.5rem;
        font-size: 0.85rem;
    }
}
</style>
