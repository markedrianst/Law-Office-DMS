import api from '@/services/api';

const exportService = {
  // Export cases
  async exportCases(params) {
    try {
      const response = await api.get('/admin/export/cases', {
        params,
        responseType: 'blob'
      });
      return response;
    } catch (error) {
      console.error('Export failed:', error);
      throw error;
    }
  },

  // Export all data
  async exportAll(params = {}) {
    try {
      const response = await api.get('/admin/export/all', {
        params,
        responseType: 'blob'
      });
      return response;
    } catch (error) {
      console.error('Export all failed:', error);
      throw error;
    }
  },

  // Download file helper
  downloadFile(response, filename) {
    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', filename);
    document.body.appendChild(link);
    link.click();
    link.remove();
    window.URL.revokeObjectURL(url);
  }
};

export default exportService;