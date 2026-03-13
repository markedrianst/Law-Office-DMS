// frontend/src/composables/useMasterData.js
import { ref, computed } from 'vue';

const loadFromStorage = (key) => {
  try {
    const data = sessionStorage.getItem(key);
    return data ? JSON.parse(data) : [];
  } catch {
    return [];
  }
};

const _categories = ref(loadFromStorage('master_categories'));
const _stages = ref(loadFromStorage('master_stages'));
const _courts = ref(loadFromStorage('master_courts'));
const _documents = ref(loadFromStorage('master_documents'));
const _users = ref(loadFromStorage('master_users'));
const _clients = ref(loadFromStorage('master_clients'));

// Loading states
const _documentsLoading = ref(false);

export function useMasterData() {
  // Getters with computed for reactivity
  const categories = computed(() => _categories.value);
  const stages = computed(() => _stages.value);
  const courts = computed(() => _courts.value);
  const documents = computed(() => _documents.value);
  const documentsLoading = computed(() => _documentsLoading.value);
  const users = computed(() => _users.value);
  const clients = computed(() => _clients.value);

  // Filtered getters
  const lawyers = computed(() => 
    _users.value.filter(u => 
      u.role?.name?.toLowerCase() === 'lawyer' || 
      u.role?.toLowerCase() === 'lawyer'
    )
  );

  const clerks = computed(() => 
    _users.value.filter(u => 
      u.role?.name?.toLowerCase() === 'clerk' || 
      u.role?.toLowerCase() === 'clerk'
    )
  );

  const activeStages = computed(() => 
    _stages.value.filter(s => s.is_active !== false)
  );

  const activeCategories = computed(() => 
    _categories.value.filter(c => c.is_active !== false)
  );

  const activeDocuments = computed(() => 
    _documents.value.filter(d => d.is_active !== false && d.approval_status === 'approved')
  );

  // Helper methods
  const getCategoryName = (id) => {
    if (!id) return '—';
    const found = _categories.value.find(c => c.id === id);
    return found?.name || '—';
  };

  const getCategoryColor = (id) => {
    if (!id) return '#1a4972';
    const found = _categories.value.find(c => c.id === id);
    return found?.color || '#1a4972';
  };

  const getStageName = (id) => {
    if (!id) return '—';
    const found = _stages.value.find(s => s.id === id);
    return found?.name || '—';
  };

  const getStageColor = (id) => {
    if (!id) return '#64748b';
    const found = _stages.value.find(s => s.id === id);
    return found?.color || '#64748b';
  };

  const getCourtName = (id) => {
    if (!id) return '—';
    const found = _courts.value.find(c => c.id === id);
    return found?.name || '—';
  };

  const getUserName = (id) => {
    if (!id) return '—';
    const found = _users.value.find(u => u.id === id);
    return found?.full_name || '—';
  };

  const getClientName = (id) => {
    if (!id) return '—';
    const found = _clients.value.find(c => c.id === id);
    return found?.full_name || '—';
  };

  const getDocumentType = (id) => {
    if (!id) return '—';
    const found = _documents.value.find(d => d.id === id);
    return found?.type || '—';
  };

  const getDocumentColor = (id) => {
    if (!id) return '#94a3b8';
    const found = _documents.value.find(d => d.id === id);
    return found?.color || '#94a3b8';
  };

  // Refresh methods (call after CRUD operations)
  const refreshCategories = (newData) => {
    _categories.value = newData;
    sessionStorage.setItem('master_categories', JSON.stringify(newData));
  };

  const refreshStages = (newData) => {
    _stages.value = newData;
    sessionStorage.setItem('master_stages', JSON.stringify(newData));
  };

  const refreshCourts = (newData) => {
    _courts.value = newData;
    sessionStorage.setItem('master_courts', JSON.stringify(newData));
  };

  const refreshDocuments = (newData) => {
    _documents.value = newData;
    sessionStorage.setItem('master_documents', JSON.stringify(newData));
  };

  const refreshUsers = (newData) => {
    _users.value = newData;
    sessionStorage.setItem('master_users', JSON.stringify(newData));
  };

  const refreshClients = (newData) => {
    _clients.value = newData;
    sessionStorage.setItem('master_clients', JSON.stringify(newData));
  };

  // Set loading state
  const setDocumentsLoading = (loading) => {
    _documentsLoading.value = loading;
  };

  // Optimistic update helpers
  const addCategory = (newItem) => {
    _categories.value.unshift(newItem);
    sessionStorage.setItem('master_categories', JSON.stringify(_categories.value));
  };

  const updateCategory = (id, updates) => {
    const index = _categories.value.findIndex(c => c.id === id);
    if (index !== -1) {
      _categories.value[index] = { ..._categories.value[index], ...updates };
      sessionStorage.setItem('master_categories', JSON.stringify(_categories.value));
    }
  };

  const removeCategory = (id) => {
    _categories.value = _categories.value.filter(c => c.id !== id);
    sessionStorage.setItem('master_categories', JSON.stringify(_categories.value));
  };

  const addClient = (newItem) => {
    _clients.value.unshift(newItem);
    sessionStorage.setItem('master_clients', JSON.stringify(_clients.value));
  };

  const updateClient = (id, updates) => {
    const index = _clients.value.findIndex(c => c.id === id);
    if (index !== -1) {
      _clients.value[index] = { ..._clients.value[index], ...updates };
      sessionStorage.setItem('master_clients', JSON.stringify(_clients.value));
    }
  };

  return {
    // Data
    categories,
    stages,
    courts,
    documents,
    documentsLoading,
    users,
    clients,
    lawyers,
    clerks,
    activeStages,
    activeCategories,
    activeDocuments,

    // Getters
    getCategoryName,
    getCategoryColor,
    getStageName,
    getStageColor,
    getCourtName,
    getUserName,
    getClientName,
    getDocumentType,
    getDocumentColor,

    // Refresh methods
    refreshCategories,
    refreshStages,
    refreshCourts,
    refreshDocuments,
    refreshUsers,
    refreshClients,
    
    // Loading state
    setDocumentsLoading,

    // Optimistic updates
    addCategory,
    updateCategory,
    removeCategory,
    addClient,
    updateClient,
  };
}