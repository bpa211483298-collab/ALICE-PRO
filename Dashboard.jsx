import AdminLayout from '@/Layouts/AdminLayout';

export default function Dashboard() {
    return (
        <div className="py-12">
            <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div className="p-6 bg-white border-b border-gray-200">
                        <h2 className="text-2xl font-semibold text-gray-800">Admin Dashboard</h2>
                        <p className="mt-2 text-gray-600">Welcome to the admin dashboard. You have administrator privileges.</p>
                        
                        <div className="mt-6">
                            <h3 className="text-lg font-medium text-gray-900">Quick Actions</h3>
                            <div className="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                                <div className="bg-blue-50 p-4 rounded-lg">
                                    <h4 className="font-medium text-blue-800">User Management</h4>
                                    <p className="mt-1 text-sm text-blue-600">Manage users and their roles</p>
                                </div>
                                <div className="bg-green-50 p-4 rounded-lg">
                                    <h4 className="font-medium text-green-800">System Settings</h4>
                                    <p className="mt-1 text-sm text-green-600">Configure system preferences</p>
                                </div>
                                <div className="bg-purple-50 p-4 rounded-lg">
                                    <h4 className="font-medium text-purple-800">Activity Logs</h4>
                                    <p className="mt-1 text-sm text-purple-600">View system activity</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}

Dashboard.layout = page => <AdminLayout children={page} title="Admin Dashboard" />;
