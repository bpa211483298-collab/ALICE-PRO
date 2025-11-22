import { Head } from '@inertiajs/react';
import ApplicationLogo from '@/Components/ApplicationLogo';
import NavLink from '@/Components/NavLink';
import { Link } from '@inertiajs/react';

export default function AdminLayout({ auth, header, children }) {
    return (
        <div className="min-h-screen bg-gray-100">
            <nav className="bg-white border-b border-gray-100">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="flex justify-between h-16">
                        <div className="flex">
                            <div className="shrink-0 flex items-center">
                                <Link href="/">
                                    <ApplicationLogo className="block h-9 w-auto fill-current text-gray-800" />
                                </Link>
                            </div>

                            <div className="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                                <NavLink href={route('admin.dashboard')} active={route().current('admin.dashboard')}>
                                    Dashboard
                                </NavLink>
                                <NavLink href={route('admin.users.index')} active={route().current('admin.users.*')}>
                                    Users
                                </NavLink>
                                <NavLink href={route('admin.settings')} active={route().current('admin.settings')}>
                                    Settings
                                </NavLink>
                            </div>
                        </div>

                        <div className="hidden sm:flex sm:items-center sm:ml-6">
                            <div className="ml-3 relative">
                                <div className="flex items-center">
                                    <div className="font-medium text-sm text-gray-500">
                                        {auth.user.name}
                                    </div>
                                    
                                    <Link
                                        href={route('profile.edit')}
                                        className="ml-4 text-sm text-gray-700 underline"
                                    >
                                        Profile
                                    </Link>

                                    <Link
                                        href={route('logout')}
                                        method="post"
                                        as="button"
                                        className="ml-4 text-sm text-gray-700 underline"
                                    >
                                        Log Out
                                    </Link>
                                </div>
                            </div>
                        </div>

                        {/* Mobile menu button */}
                        <div className="-mr-2 flex items-center sm:hidden">
                            <button className="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                                <svg className="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                    <path className="inline-flex" strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 6h16M4 12h16M4 18h16" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                {/* Responsive menu */}
                <div className="sm:hidden">
                    <div className="pt-2 pb-3 space-y-1">
                        <NavLink href={route('admin.dashboard')} active={route().current('admin.dashboard')} className="block pl-3">
                            Dashboard
                        </NavLink>
                        <NavLink href={route('admin.users.index')} active={route().current('admin.users.*')} className="block pl-3">
                            Users
                        </NavLink>
                        <NavLink href={route('admin.settings')} active={route().current('admin.settings')} className="block pl-3">
                            Settings
                        </NavLink>
                    </div>

                    <div className="pt-4 pb-3 border-t border-gray-200">
                        <div className="flex items-center px-4">
                            <div className="font-medium text-base text-gray-800">{auth.user.name}</div>
                            <div className="font-medium text-sm text-gray-500 ml-2">({auth.user.roles?.[0]?.name || 'User'})</div>
                        </div>

                        <div className="mt-3 space-y-1">
                            <Link
                                href={route('profile.edit')}
                                className="block px-4 py-2 text-base font-medium text-gray-500 hover:text-gray-800 hover:bg-gray-100"
                            >
                                Profile
                            </Link>
                            <Link
                                href={route('logout')}
                                method="post"
                                as="button"
                                className="block w-full text-left px-4 py-2 text-base font-medium text-gray-500 hover:text-gray-800 hover:bg-gray-100"
                            >
                                Log Out
                            </Link>
                        </div>
                    </div>
                </div>
            </nav>

            {header && (
                <header className="bg-white shadow">
                    <div className="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        <h1 className="text-3xl font-bold text-gray-900">
                            {header}
                        </h1>
                    </div>
                </header>
            )}

            <main>
                {children}
            </main>
        </div>
    );
}
