import { u as useApi } from './server.mjs';

const useProjects = () => {
  const { get, post, put, delete: del } = useApi();
  const getProjects = async (params = {}) => {
    return await get("/projects", params);
  };
  const getProject = async (id) => {
    return await get(`/projects/${id}`);
  };
  const createProject = async (projectData) => {
    return await post("/projects", projectData);
  };
  const updateProject = async (id, projectData) => {
    return await put(`/projects/${id}`, projectData);
  };
  const deleteProject = async (id) => {
    return await del(`/projects/${id}`);
  };
  const updateProjectStatus = async (id, status) => {
    return await put(`/projects/${id}/status`, { status });
  };
  const getProjectMilestones = async (id) => {
    return await get(`/projects/${id}/milestones`);
  };
  const createProjectMilestone = async (projectId, milestoneData) => {
    return await post(`/projects/${projectId}/milestones`, milestoneData);
  };
  const updateProjectMilestone = async (projectId, milestoneId, milestoneData) => {
    return await put(`/projects/${projectId}/milestones/${milestoneId}`, milestoneData);
  };
  const deleteProjectMilestone = async (projectId, milestoneId) => {
    return await del(`/projects/${projectId}/milestones/${milestoneId}`);
  };
  const getProjectStats = async (params = {}) => {
    return await get("/projects/stats", params);
  };
  const getProjectsByCategory = async (category, params = {}) => {
    return await get(`/projects/category/${category}`, params);
  };
  const getProjectsByStatus = async (status, params = {}) => {
    return await get(`/projects/status/${status}`, params);
  };
  const exportProjects = async (format = "excel", params = {}) => {
    return await get(`/projects/export/${format}`, params);
  };
  return {
    getProjects,
    getProject,
    createProject,
    updateProject,
    deleteProject,
    updateProjectStatus,
    getProjectMilestones,
    createProjectMilestone,
    updateProjectMilestone,
    deleteProjectMilestone,
    getProjectStats,
    getProjectsByCategory,
    getProjectsByStatus,
    exportProjects
  };
};

export { useProjects as u };
//# sourceMappingURL=useProjects-BpvOU6mv.mjs.map
