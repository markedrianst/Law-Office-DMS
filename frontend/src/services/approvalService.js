// src/services/approvalService.js
import api from './api';

class SmartPolling {
    constructor(endpoint, interval = 30000) {
        this.endpoint = endpoint;
        this.interval = interval;
        this.timer = null;
        this.lastPoll = 0;
        this.lastData = null;
        this.callbacks = new Set();
    }

    subscribe(callback) {
        this.callbacks.add(callback);
        if (this.callbacks.size === 1) this.start();
        return () => this.unsubscribe(callback);
    }

    unsubscribe(callback) {
        this.callbacks.delete(callback);
        if (this.callbacks.size === 0) this.stop();
    }

    async poll() {
        try {
            const params = { last_poll: this.lastPoll };
            if (this.lastData?.count !== undefined) {
                params.last_count = this.lastData.count;
            }

            const response = await api.get(this.endpoint, { params });
            
            if (response.data.changed) {
                this.lastPoll = response.data.timestamp || Date.now() / 1000;
                this.lastData = response.data;
                this.notify(response.data);
            }
        } catch (error) {
            console.error(`Polling error for ${this.endpoint}:`, error);
        }
    }

    notify(data) {
        this.callbacks.forEach(cb => cb(data));
    }

    start() {
        this.poll(); // Immediate first poll
        this.timer = setInterval(() => this.poll(), this.interval);
    }

    stop() {
        if (this.timer) {
            clearInterval(this.timer);
            this.timer = null;
        }
    }

    forceRefresh() {
        this.lastPoll = 0;
        this.poll();
    }
}

// Create polling instances
export const approvalsPolling = new SmartPolling('/admin/approvals', 30000);
export const pendingCountPolling = new SmartPolling('/admin/approvals/pending-count', 10000);

// API methods
export const getApprovals = async (params = {}) => {
    const response = await api.get('/admin/approvals', { params });
    return response.data;
};

export const getPendingCount = async () => {
    const response = await api.get('/admin/approvals/pending-count');
    return response.data.count ?? 0;
};

export const reviewMovement = async (type, id, status, notes = '') => {
    const response = await api.patch(`/admin/approvals/${type}/${id}/approve`, {
        status,
        notes
    });
    
    // Force refresh after action
    approvalsPolling.forceRefresh();
    pendingCountPolling.forceRefresh();
    
    return response.data;
};

export const reviewChecklistMovement = (caseId, movementId, status, notes) => 
    reviewMovement('checklist', movementId, status, notes);

export const reviewFolderMovement = (caseId, movementId, status, notes) => 
    reviewMovement('folder', movementId, status, notes);