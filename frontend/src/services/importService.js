import api from '@/services/api';

const importService = {
  // Import Excel file
  async importExcel(formData) {
    const { data } = await api.post('/admin/case/import', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    });
    
    return data;
  },

  // Get import history (optional)
  async getImportHistory() {
    const { data } = await api.get('/admin/case/import-history');
    return data;
  },

  // Get import template (optional)
  async getTemplate() {
    const response = await api.get('/admin/case/import-template', {
      responseType: 'blob'
    });
    return response;
  }
};

export default importService;