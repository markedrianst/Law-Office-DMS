import api from "@/services/api";
import { setUsers } from "@/utils/appUtils";

class UserService {
  async getRoles() {
    const { data } = await api.get("/roles");
    return data;
  }

  async getUsers(params = {}) {
    console.log('📡 API CALL: Fetching users from API...');
    try {
      const { data } = await api.get("/users", { params });
      console.log('📡 API RESPONSE:', data);
      
      if (data.data) {
        const transformedUsers = data.data.map(user => ({
          id: user.id,
          name: user.name || '',
          email: user.email,
          role: user.role,
          status: user.status === 'Active' ? 'Active' : 'Inactive',
          created_at: user.created_at,
          last_login: user.last_login,
          address: user.address,
          contact_number: user.contact_number
        }));
        
        // 🔥 THIS IS CRITICAL - Store in appUtils
        console.log('📦 Storing users in appUtils:', transformedUsers.length);
        setUsers(transformedUsers);
        
        data.data = transformedUsers;
      }
      
      return data;
    } catch (error) {
      console.error('API Error:', error);
      throw error;
    }
  }

  async getUserById(id) {
    const { data } = await api.get(`/users/${id}`);
    return data;
  }

  async createUser(userData) {
    const { data } = await api.post("/users", userData);
    
    // After creating, refresh the list
    if (data.data) {
      await this.getUsers({ per_page: 100 });
    }
    
    return data;
  }

  async updateUser(id, userData) {
    const { data } = await api.put(`/users/${id}`, userData);
    
    // After updating, refresh the list
    if (data.data) {
      await this.getUsers({ per_page: 100 });
    }
    
    return data;
  }

  async deleteUser(id) {
    const { data } = await api.delete(`/users/${id}`);
    
    // After deleting, refresh the list
    await this.getUsers({ per_page: 100 });
    
    return data;
  }

  async toggleUserStatus(id) {
    const { data } = await api.patch(`/users/${id}/toggle-status`);
    
    // After toggling, refresh the list
    if (data.data) {
      await this.getUsers({ per_page: 100 });
    }
    
    return data;
  }
}

export default new UserService();