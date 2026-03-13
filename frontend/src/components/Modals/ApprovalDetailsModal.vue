<template>
  <div>
    <!-- Your clerk dashboard -->
    <button @click="openDetails(item)">View Details</button>
    
    <!-- Reuse the same modal -->
    <ApprovalDetailsModal
      :show="detailsModal.show"
      :item="detailsModal.item"
      @close="detailsModal.show = false"
    />
  </div>
</template>

<script setup>
import { reactive } from 'vue';
import ApprovalDetailsModal from '@/components/Modals/ApprovalDetailsModal.vue';

const detailsModal = reactive({
  show: false,
  item: null
});

const openDetails = (item) => {
  detailsModal.item = item;
  detailsModal.show = true;
};
</script>