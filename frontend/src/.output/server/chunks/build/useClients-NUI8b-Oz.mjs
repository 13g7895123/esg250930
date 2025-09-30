import { u as useApi } from './server.mjs';

const useClients = () => {
  const { get, post, put, delete: del } = useApi();
  const getClients = async (params = {}) => {
    return await get("/clients", params);
  };
  const getClient = async (id) => {
    return await get(`/clients/${id}`);
  };
  const createClient = async (clientData) => {
    return await post("/clients", clientData);
  };
  const updateClient = async (id, clientData) => {
    return await put(`/clients/${id}`, clientData);
  };
  const deleteClient = async (id) => {
    return await del(`/clients/${id}`);
  };
  const getClientStats = async (id) => {
    return await get(`/clients/${id}/stats`);
  };
  const getClientProjects = async (id, params = {}) => {
    return await get(`/clients/${id}/projects`, params);
  };
  const addContactMethod = async (clientId, contactData) => {
    return await post(`/clients/${clientId}/contacts`, contactData);
  };
  const updateContactMethod = async (clientId, contactId, contactData) => {
    return await put(`/clients/${clientId}/contacts/${contactId}`, contactData);
  };
  const deleteContactMethod = async (clientId, contactId) => {
    return await del(`/clients/${clientId}/contacts/${contactId}`);
  };
  return {
    getClients,
    getClient,
    createClient,
    updateClient,
    deleteClient,
    getClientStats,
    getClientProjects,
    addContactMethod,
    updateContactMethod,
    deleteContactMethod
  };
};

export { useClients as u };
//# sourceMappingURL=useClients-NUI8b-Oz.mjs.map
