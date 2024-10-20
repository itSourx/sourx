import { defineStore } from 'pinia'
import axios from 'axios'

export const useAdminStore = defineStore('adminStore', {
  state: () => ({
    totalUsers: 0,
    totalDocuments: 0,
    pendingRequests: 0,
    acceptedRequests: 0,
    rejectedRequests: 0,
    documentsByMonth: [],
    documentsByStatus: [],
    recentDocuments: [],
    storageDetails: [],
    requestsByStatus: {
      pending: 0,
      accepted: 0,
      rejected: 0
    },
    usedStorage: 0,
    totalStorage: 128, // Supposons que la capacité totale est de 128 GB
    documentStorage: 0,
    mediaStorage: 0,
    otherStorage: 0
  }),
  actions: {
    async fetchStatistics(token) {
      try {
        const headers = {
          Authorization: `Bearer ${token}`
        }

        const [userResponse, documentResponse, requestResponse, storageResponse] =
          await Promise.all([
            axios.get('/getUsersStats', { headers }),
            axios.get('/getDocumentsStats', { headers }),
            axios.get('/getRequestsStats', { headers }),
            axios.get('/getStorageStats', { headers })
          ])

        // User Statistics
        this.totalUsers = userResponse.data.totalUsers

        // Document Statistics
        this.totalDocuments = documentResponse.data.totalDocuments
        this.documentsByMonth = documentResponse.data.documentsByMonth
        this.documentsByStatus = documentResponse.data.documentsByStatus
        this.recentDocuments = documentResponse.data.recentDocuments

        // Request Statistics
        this.totalRequests = requestResponse.data.totalRequests
        this.requestsByStatus.pending = requestResponse.data.pendingRequests
        this.requestsByStatus.accepted = requestResponse.data.acceptedRequests
        this.requestsByStatus.rejected = requestResponse.data.rejectedRequests

        // Storage Statistics
        this.storageDetails = storageResponse.data.storageDetails
        this.usedStorage = storageResponse.data.usedStorage
        this.documentStorage = storageResponse.data.documentStorage
        this.mediaStorage = storageResponse.data.mediaStorage
        this.otherStorage = storageResponse.data.otherStorage
      } catch (error) {
        console.error('Failed to fetch statistics:', error)
      }
    },
    async deleteDocument(documentId) {
      try {
        await axios.delete(`/deleteDocument/${documentId}`, {
          headers: {
            Authorization: `Bearer ${localStorage.getItem('jwt_token')}`
          }
        })
        this.fetchStatistics(localStorage.getItem('jwt_token'))
      } catch (error) {
        console.error('Failed to delete document:', error)
      }
    }
  }
})
