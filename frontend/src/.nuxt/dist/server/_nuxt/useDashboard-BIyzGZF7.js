import { u as useApi } from "../server.mjs";
const useDashboard = () => {
  const { get } = useApi();
  const getDashboardStats = async () => {
    return await get("/dashboard/stats");
  };
  const getRevenueStats = async (period = "month", params = {}) => {
    return await get(`/dashboard/revenue/${period}`, params);
  };
  const getProjectCountStats = async (period = "month", params = {}) => {
    return await get(`/dashboard/projects/${period}`, params);
  };
  const getClientStats = async (params = {}) => {
    return await get("/dashboard/clients", params);
  };
  const getRecentActivities = async (limit = 10) => {
    return await get("/dashboard/activities", { limit });
  };
  const getUpcomingDeadlines = async (days = 30) => {
    return await get("/dashboard/deadlines", { days });
  };
  const getMonthlyRevenueTrend = async (months = 12) => {
    return await get("/dashboard/revenue/trend", { months });
  };
  const getProjectStatusDistribution = async () => {
    return await get("/dashboard/projects/status-distribution");
  };
  const getCategoryRevenueBreakdown = async (period = "year") => {
    return await get("/dashboard/revenue/by-category", { period });
  };
  const getTopClientsByRevenue = async (limit = 10, period = "year") => {
    return await get("/dashboard/clients/top-revenue", { limit, period });
  };
  const getDailyStats = async (startDate, endDate) => {
    return await get("/dashboard/daily-stats", {
      start_date: startDate,
      end_date: endDate
    });
  };
  const getWeeklyStats = async (startDate, endDate) => {
    return await get("/dashboard/weekly-stats", {
      start_date: startDate,
      end_date: endDate
    });
  };
  const getYearlyStats = async (year) => {
    return await get("/dashboard/yearly-stats", { year });
  };
  return {
    getDashboardStats,
    getRevenueStats,
    getProjectCountStats,
    getClientStats,
    getRecentActivities,
    getUpcomingDeadlines,
    getMonthlyRevenueTrend,
    getProjectStatusDistribution,
    getCategoryRevenueBreakdown,
    getTopClientsByRevenue,
    getDailyStats,
    getWeeklyStats,
    getYearlyStats
  };
};
export {
  useDashboard as u
};
//# sourceMappingURL=useDashboard-BIyzGZF7.js.map
