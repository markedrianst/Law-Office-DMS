<template>
  <div class="space-y-4 sm:space-y-6">

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
      <!-- Total Cases -->
      <div 
        class="bg-white rounded-xl shadow-sm border border-slate-100 p-4 sm:p-5 hover:shadow-md transition-all duration-300 group"
        style="animation: fadeInUp 0.3s ease-out"
      >
        <div class="flex items-start justify-between mb-3">
          <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center shadow-lg shadow-blue-500/30 group-hover:scale-110 transition-transform">
            <svg class="w-5 h-5 sm:w-6 sm:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
            </svg>
          </div>
          <span class="text-xs font-medium text-slate-400 uppercase tracking-wide">Total Cases</span>
        </div>
        <div class="text-2xl sm:text-3xl font-bold text-slate-800 mb-2">{{ displayStats.total_cases || 0 }}</div>
        <div class="flex items-center gap-2 flex-wrap">
          <span class="text-xs bg-emerald-50 text-emerald-700 px-2 py-1 rounded-full font-semibold">
            {{ displayStats.active_cases || 0 }} Active
          </span>
          <span class="text-xs text-slate-400">{{ (displayStats.total_cases - displayStats.active_cases) || 0 }} Inactive</span>
        </div>
      </div>

      <!-- Total Users -->
      <div 
        class="bg-white rounded-xl shadow-sm border border-slate-100 p-4 sm:p-5 hover:shadow-md transition-all duration-300 group"
        style="animation: fadeInUp 0.3s ease-out 0.1s both"
      >
        <div class="flex items-start justify-between mb-3">
          <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-xl bg-gradient-to-br from-purple-500 to-purple-600 flex items-center justify-center shadow-lg shadow-purple-500/30 group-hover:scale-110 transition-transform">
            <svg class="w-5 h-5 sm:w-6 sm:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
          </div>
          <span class="text-xs font-medium text-slate-400 uppercase tracking-wide">Total Users</span>
        </div>
        <div class="text-2xl sm:text-3xl font-bold text-slate-800 mb-2">{{ displayAdminStats.total_users || 0 }}</div>
        <div class="flex items-center gap-2 flex-wrap">
          <span class="text-xs bg-blue-50 text-blue-700 px-2 py-1 rounded-full font-semibold">
            {{ displayAdminStats.lawyers || 0 }} Lawyers
          </span>
          <span class="text-xs bg-emerald-50 text-emerald-700 px-2 py-1 rounded-full font-semibold">
            {{ displayAdminStats.clerks || 0 }} Clerks
          </span>
        </div>
      </div>

      <!-- Pending Approvals -->
      <div 
        class="bg-white rounded-xl shadow-sm border border-slate-100 p-4 sm:p-5 hover:shadow-md transition-all duration-300 group"
        style="animation: fadeInUp 0.3s ease-out 0.2s both"
      >
        <div class="flex items-start justify-between mb-3">
          <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-xl bg-gradient-to-br from-amber-500 to-amber-600 flex items-center justify-center shadow-lg shadow-amber-500/30 group-hover:scale-110 transition-transform">
            <svg class="w-5 h-5 sm:w-6 sm:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
          <span class="text-xs font-medium text-slate-400 uppercase tracking-wide">Pending</span>
        </div>
        <div class="text-2xl sm:text-3xl font-bold text-slate-800 mb-2">{{ displayPendingTotal }}</div>
        <div class="flex items-center gap-2 flex-wrap">
          <span class="text-xs bg-amber-50 text-amber-700 px-2 py-1 rounded-full font-semibold">
            {{ displayPendingDocuments }} Docs
          </span>
          <span class="text-xs bg-blue-50 text-blue-700 px-2 py-1 rounded-full font-semibold">
            {{ displayPendingMovements }} Moves
          </span>
        </div>
      </div>

      <!-- Total Clients -->
      <div 
        class="bg-white rounded-xl shadow-sm border border-slate-100 p-4 sm:p-5 hover:shadow-md transition-all duration-300 group"
        style="animation: fadeInUp 0.3s ease-out 0.3s both"
      >
        <div class="flex items-start justify-between mb-3">
          <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-xl bg-gradient-to-br from-emerald-500 to-emerald-600 flex items-center justify-center shadow-lg shadow-emerald-500/30 group-hover:scale-110 transition-transform">
            <svg class="w-5 h-5 sm:w-6 sm:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
          </div>
          <span class="text-xs font-medium text-slate-400 uppercase tracking-wide">Clients</span>
        </div>
        <div class="text-2xl sm:text-3xl font-bold text-slate-800 mb-2">{{ displayStats.total_clients || 0 }}</div>
        <div class="text-xs text-slate-500 mt-1">Registered clients</div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div 
      class="bg-white rounded-xl shadow-sm border border-slate-100 p-4 sm:p-5"
      style="animation: fadeInUp 0.3s ease-out 0.4s both"
    >
      <div class="flex items-center gap-2 mb-4">
        <div class="w-1 h-5 rounded-full bg-gradient-to-b from-[#1a4972] to-[#2d6db5]"></div>
        <h3 class="text-sm sm:text-base font-semibold text-slate-700">Quick Actions</h3>
      </div>
      <div class="grid grid-cols-2 sm:flex sm:flex-wrap gap-2">
        <router-link 
          to="/casemaster?action=new" 
          class="px-3 sm:px-4 py-2.5 bg-gradient-to-r from-blue-50 to-blue-100 text-blue-700 rounded-xl text-xs sm:text-sm font-semibold hover:from-blue-100 hover:to-blue-200 transition-all flex items-center justify-center gap-2 shadow-sm hover:shadow-md group"
        >
          <svg class="w-4 h-4 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
          <span class="hidden sm:inline">New Case</span>
          <span class="sm:hidden">Case</span>
        </router-link>
        
        <router-link 
          to="/approvals" 
          class="px-3 sm:px-4 py-2.5 bg-gradient-to-r from-amber-50 to-amber-100 text-amber-700 rounded-xl text-xs sm:text-sm font-semibold hover:from-amber-100 hover:to-amber-200 transition-all flex items-center justify-center gap-2 shadow-sm hover:shadow-md group"
        >
          <svg class="w-4 h-4 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
          </svg>
          <span class="hidden sm:inline">Approvals</span>
          <span class="sm:hidden">Approve</span>
        </router-link>
        
        <router-link 
          to="/calendar" 
          class="px-3 sm:px-4 py-2.5 bg-gradient-to-r from-emerald-50 to-emerald-100 text-emerald-700 rounded-xl text-xs sm:text-sm font-semibold hover:from-emerald-100 hover:to-emerald-200 transition-all flex items-center justify-center gap-2 shadow-sm hover:shadow-md group"
        >
          <svg class="w-4 h-4 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
          </svg>
          <span class="hidden sm:inline">Schedule</span>
          <span class="sm:hidden">Calendar</span>
        </router-link>
        
        <router-link 
          to="/reports" 
          class="px-3 sm:px-4 py-2.5 bg-gradient-to-r from-purple-50 to-purple-100 text-purple-700 rounded-xl text-xs sm:text-sm font-semibold hover:from-purple-100 hover:to-purple-200 transition-all flex items-center justify-center gap-2 shadow-sm hover:shadow-md group"
        >
          <svg class="w-4 h-4 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
          </svg>
          <span class="hidden sm:inline">Reports</span>
          <span class="sm:hidden">Report</span>
        </router-link>
      </div>
    </div>

    <!-- Today's Schedule -->
    <div 
      class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden"
      style="animation: fadeInUp 0.3s ease-out 0.5s both"
    >
      <div class="px-4 sm:px-5 py-4 border-b border-slate-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2">
        <div class="flex items-center gap-2">
          <div class="w-8 h-8 rounded-lg bg-[#1a4972]/10 flex items-center justify-center">
            <svg class="w-4 h-4 sm:w-5 sm:h-5 text-[#1a4972]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
          </div>
          <h3 class="text-sm sm:text-base font-semibold text-slate-700">Today's Schedule</h3>
        </div>
        <span class="text-xs bg-blue-50 text-blue-700 px-3 py-1 rounded-full font-semibold">
          {{ todaySchedules.length }} {{ todaySchedules.length === 1 ? 'event' : 'events' }}
        </span>
      </div>
      
      <div class="divide-y divide-slate-50 max-h-64 sm:max-h-80 overflow-y-auto">
        <div 
          v-for="(schedule, index) in todaySchedules" 
          :key="schedule.id" 
          class="px-4 sm:px-5 py-3 hover:bg-slate-50/50 transition-colors"
          :style="{ animation: `fadeIn 0.3s ease-out ${index * 0.05}s both` }"
        >
          <div class="flex flex-col sm:flex-row items-start gap-3">
            <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-xl bg-gradient-to-br from-blue-50 to-blue-100 flex items-center justify-center flex-shrink-0 shadow-sm">
              <span class="text-xs font-bold text-blue-700">{{ formatTime(schedule.hearing_date) }}</span>
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-semibold text-slate-800 mb-1 truncate">{{ schedule.title }}</p>
              <p class="text-xs text-slate-500">
                <span class="font-medium">{{ schedule.case?.case_code || 'N/A' }}</span>
                <span class="mx-1">•</span>
                <span>{{ schedule.location || 'No location' }}</span>
              </p>
            </div>
            <span class="px-2.5 py-1 text-xs font-semibold rounded-lg self-start" :class="statusClass(schedule.status)">
              {{ schedule.status }}
            </span>
          </div>
        </div>
        
        <div v-if="!todaySchedules.length" class="px-5 py-12 text-center">
          <div class="w-12 h-12 rounded-xl bg-slate-100 flex items-center justify-center mx-auto mb-3">
            <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
          </div>
          <p class="text-sm text-slate-500 font-medium">No schedules for today</p>
          <p class="text-xs text-slate-400 mt-1">Your calendar is clear!</p>
        </div>
      </div>
    </div>

    <!-- Two Column Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
      <!-- Recent Users -->
      <div 
        class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden"
        style="animation: fadeInUp 0.3s ease-out 0.6s both"
      >
        <div class="px-4 sm:px-5 py-4 border-b border-slate-100 flex items-center justify-between">
          <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-lg bg-purple-100 flex items-center justify-center">
              <svg class="w-4 h-4 sm:w-5 sm:h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
              </svg>
            </div>
            <h3 class="text-sm sm:text-base font-semibold text-slate-700">Recent Users</h3>
          </div>
          <router-link 
            to="/usermanagement" 
            class="text-xs text-[#1a4972] hover:text-[#0f2f4a] font-semibold flex items-center gap-1 group"
          >
            <span class="hidden sm:inline">View All</span>
            <svg class="w-4 h-4 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
          </router-link>
        </div>
        
        <div class="divide-y divide-slate-50 max-h-80 overflow-y-auto">
          <div 
            v-for="(user, index) in recentUsers" 
            :key="user.id" 
            class="px-4 sm:px-5 py-3 hover:bg-slate-50/50 transition-colors"
            :style="{ animation: `fadeIn 0.3s ease-out ${index * 0.05}s both` }"
          >
            <div class="flex items-center gap-3">
              <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-gradient-to-br from-[#1a4972] to-[#2d6db5] flex items-center justify-center text-white text-xs sm:text-sm font-bold shadow-md flex-shrink-0">
                {{ getUserInitials(user.full_name) }}
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-slate-800 truncate">{{ user.full_name }}</p>
                <div class="flex items-center gap-2 mt-0.5 flex-wrap">
                  <span class="text-xs px-2 py-0.5 rounded-full font-medium" :class="roleBadgeClass(user.role)">
                    {{ user.role }}
                  </span>
                  <span class="text-xs text-slate-400 hidden sm:inline">{{ formatDate(user.created_at) }}</span>
                </div>
              </div>
              <div class="flex items-center gap-1 flex-shrink-0">
                <span class="w-2 h-2 rounded-full" :class="user.status === 'active' ? 'bg-emerald-500' : 'bg-slate-300'"></span>
                <span class="text-xs font-medium hidden sm:inline" :class="user.status === 'active' ? 'text-emerald-600' : 'text-slate-400'">
                  {{ user.status }}
                </span>
              </div>
            </div>
          </div>
          
          <div v-if="!recentUsers.length" class="px-5 py-12 text-center">
            <div class="w-12 h-12 rounded-xl bg-slate-100 flex items-center justify-center mx-auto mb-3">
              <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
              </svg>
            </div>
            <p class="text-sm text-slate-500 font-medium">No recent users</p>
          </div>
        </div>
      </div>

      <!-- Upcoming Schedules -->
      <div 
        class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden"
        style="animation: fadeInUp 0.3s ease-out 0.7s both"
      >
        <div class="px-4 sm:px-5 py-4 border-b border-slate-100 flex items-center justify-between">
          <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-lg bg-amber-100 flex items-center justify-center">
              <svg class="w-4 h-4 sm:w-5 sm:h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
            </div>
            <h3 class="text-sm sm:text-base font-semibold text-slate-700">Upcoming Schedules</h3>
          </div>
          <router-link 
            to="/calendar" 
            class="text-xs text-[#1a4972] hover:text-[#0f2f4a] font-semibold flex items-center gap-1 group"
          >
            <span class="hidden sm:inline">Calendar</span>
            <svg class="w-4 h-4 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
          </router-link>
        </div>
        
        <div class="divide-y divide-slate-50 max-h-80 overflow-y-auto">
          <div 
            v-for="(schedule, index) in upcomingSchedules" 
            :key="schedule.id" 
            class="px-4 sm:px-5 py-3 hover:bg-slate-50/50 transition-colors"
            :style="{ animation: `fadeIn 0.3s ease-out ${index * 0.05}s both` }"
          >
            <div class="flex items-start gap-3">
              <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-xl bg-gradient-to-br from-amber-50 to-amber-100 flex flex-col items-center justify-center flex-shrink-0 shadow-sm">
                <span class="text-xs font-bold text-amber-700">{{ formatDate(schedule.hearing_date).split(' ')[0] }}</span>
                <span class="text-[10px] text-amber-600">{{ formatDate(schedule.hearing_date).split(' ')[1] }}</span>
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-slate-800 mb-1 truncate">{{ schedule.title }}</p>
                <p class="text-xs text-slate-500">
                  <span class="font-medium">{{ schedule.case?.case_code || 'N/A' }}</span>
                  <span class="mx-1">•</span>
                  <span>{{ formatTime(schedule.hearing_date) }}</span>
                </p>
              </div>
            </div>
          </div>
          
          <div v-if="!upcomingSchedules.length" class="px-5 py-12 text-center">
            <div class="w-12 h-12 rounded-xl bg-slate-100 flex items-center justify-center mx-auto mb-3">
              <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
            </div>
            <p class="text-sm text-slate-500 font-medium">No upcoming schedules</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Recent Activities -->
    <div 
      class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden"
      style="animation: fadeInUp 0.3s ease-out 0.8s both"
    >
      <div class="px-4 sm:px-5 py-4 border-b border-slate-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2">
        <div class="flex items-center gap-2">
          <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center">
            <svg class="w-4 h-4 sm:w-5 sm:h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
          <h3 class="text-sm sm:text-base font-semibold text-slate-700">Recent System Activities</h3>
        </div>
        <span class="text-xs text-slate-400 bg-slate-50 px-3 py-1 rounded-full">
          {{ displayRecentActivities.length }} {{ displayRecentActivities.length === 1 ? 'activity' : 'activities' }}
        </span>
      </div>
      
      <div class="divide-y divide-slate-50 max-h-96 overflow-y-auto">
        <div 
          v-for="(activity, index) in displayRecentActivities" 
          :key="index" 
          class="px-4 sm:px-5 py-3 hover:bg-slate-50/50 transition-colors"
          :style="{ animation: `fadeIn 0.3s ease-out ${index * 0.03}s both` }"
        >
          <div class="flex items-start gap-3">
            <div class="w-2 h-2 rounded-full bg-blue-500 mt-2 flex-shrink-0"></div>
            <div class="flex-1 min-w-0">
              <p class="text-sm text-slate-700">
                <span class="font-semibold text-slate-800">{{ activity.user_name || 'System' }}</span>
                <span class="ml-1">{{ activity.action }}</span>
              </p>
              <p class="text-xs text-slate-400 mt-0.5">{{ formatDateTime(activity.created_at) }}</p>
            </div>
          </div>
        </div>
        
        <div v-if="!displayRecentActivities.length" class="px-5 py-12 text-center">
          <div class="w-12 h-12 rounded-xl bg-slate-100 flex items-center justify-center mx-auto mb-3">
            <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
          <p class="text-sm text-slate-500 font-medium">No recent activities</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { 
  formatDateTime,
  formatDate,
  formatTime,
  listenForUpdates,
  getUsers,
  getClients,
  getInitials
} from '@/utils/appUtils'

const props = defineProps({
  stats: { type: Object, default: () => ({}) },
  adminStats: { type: Object, default: () => ({}) },
  recentActivities: { type: Array, default: () => [] },
  pendingDocuments: { type: [Number, String], default: 0 },
  pendingMovements: { type: [Number, String], default: 0 },
  pendingTotal: { type: [Number, String], default: 0 },
  systemInfo: { type: Object, default: () => ({}) },
  todaySchedules: { type: Array, default: () => [] },
  upcomingSchedules: { type: Array, default: () => [] },
  recentUsers: { type: Array, default: () => [] },
  storageStats: { type: Object, default: () => ({}) }
})

// Reactive local copies
const displayStats = ref({ ...props.stats })
const displayAdminStats = ref({ ...props.adminStats })
const displayRecentActivities = ref([...props.recentActivities])
const displayPendingDocuments = ref(Number(props.pendingDocuments))
const displayPendingMovements = ref(Number(props.pendingMovements))
const displayPendingTotal = ref(Number(props.pendingTotal))

// Computed
const totalRecords = computed(() => {
  const cases = props.stats?.total_cases || 0
  const users = props.adminStats?.total_users || 0
  const clients = props.stats?.total_clients || 0
  return (cases + users + clients).toLocaleString()
})

// Helper functions
const getUserInitials = (name) => getInitials(name)

const roleBadgeClass = (role) => {
  const classes = {
    'admin': 'bg-purple-50 text-purple-700',
    'lawyer': 'bg-blue-50 text-blue-700',
    'clerk': 'bg-emerald-50 text-emerald-700'
  }
  return classes[role?.toLowerCase()] || 'bg-slate-50 text-slate-600'
}

const statusClass = (status) => {
  const classes = {
    'scheduled': 'bg-blue-50 text-blue-700 border border-blue-200',
    'completed': 'bg-emerald-50 text-emerald-700 border border-emerald-200',
    'cancelled': 'bg-red-50 text-red-700 border border-red-200',
    'rescheduled': 'bg-amber-50 text-amber-700 border border-amber-200'
  }
  return classes[status] || 'bg-slate-50 text-slate-600 border border-slate-200'
}

// Update handlers
const handleUsersUpdate = (event) => {
  const users = event.detail || []
  const lawyers = users.filter(u => u.role?.toLowerCase() === 'lawyer').length
  const clerks = users.filter(u => u.role?.toLowerCase() === 'clerk').length
  
  displayAdminStats.value = {
    ...displayAdminStats.value,
    total_users: users.length,
    lawyers,
    clerks
  }
}

const handleClientsUpdate = (event) => {
  const clients = event.detail || []
  displayStats.value = {
    ...displayStats.value,
    total_clients: clients.length
  }
}

const handleDashboardUpdate = (event) => {
  const dashboard = event.detail
  if (dashboard) {
    if (dashboard.stats) displayStats.value = { ...dashboard.stats }
    if (dashboard.adminStats) displayAdminStats.value = { ...dashboard.adminStats }
    if (dashboard.recentActivities) displayRecentActivities.value = [...dashboard.recentActivities]
    displayPendingDocuments.value = dashboard.adminStats?.pending_documents || 0
    displayPendingMovements.value = dashboard.adminStats?.pending_movements || 0
    displayPendingTotal.value = dashboard.adminStats?.pending_total || 0
  }
}

// Cleanup
let cleanupUsers, cleanupClients, cleanupDashboard

onMounted(() => {
  cleanupUsers = listenForUpdates('users-updated', handleUsersUpdate)
  cleanupClients = listenForUpdates('clients-updated', handleClientsUpdate)
  cleanupDashboard = listenForUpdates('dashboard-updated', handleDashboardUpdate)
  
  // Initial sync
  const users = getUsers()
  if (users?.length) {
    const lawyers = users.filter(u => u.role?.toLowerCase() === 'lawyer').length
    const clerks = users.filter(u => u.role?.toLowerCase() === 'clerk').length
    displayAdminStats.value = {
      ...displayAdminStats.value,
      total_users: users.length,
      lawyers,
      clerks
    }
  }
  
  const clients = getClients()
  if (clients?.length) {
    displayStats.value = {
      ...displayStats.value,
      total_clients: clients.length
    }
  }
})

onUnmounted(() => {
  if (cleanupUsers) cleanupUsers()
  if (cleanupClients) cleanupClients()
  if (cleanupDashboard) cleanupDashboard()
})
</script>

<style scoped>
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

/* Custom scrollbar for desktop */
@media (min-width: 1024px) {
  .overflow-y-auto::-webkit-scrollbar {
    width: 6px;
  }
  
  .overflow-y-auto::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 10px;
  }
  
  .overflow-y-auto::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 10px;
  }
  
  .overflow-y-auto::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
  }
}

/* Hide scrollbar on mobile for cleaner look */
@media (max-width: 1023px) {
  ::-webkit-scrollbar {
    display: none;
  }
  
  * {
    -ms-overflow-style: none;
    scrollbar-width: none;
  }
}
</style>