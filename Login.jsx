import Checkbox from '@/Components/Checkbox';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import GuestLayout from '@/Layouts/GuestLayout';
import { Head, Link, useForm, router } from '@inertiajs/react';
import { FaGithub, FaGoogle, FaApple, FaMicrophone, FaMicrophoneSlash } from 'react-icons/fa';
import VoiceInput from '@/Components/VoiceInput';
import { useState, useEffect } from 'react';
import { processLoginCommand } from '@/utils/voiceCommands';

export default function Login({ status, canResetPassword }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        email: '',
        password: '',
        remember: false,
    });
    
    const [activeField, setActiveField] = useState(null);
    const [voiceFeedback, setVoiceFeedback] = useState('');
    
    const handleVoiceResult = (result) => {
        if (activeField === 'email') {
            setData('email', result);
            setVoiceFeedback(`Email set to: ${result}`);
        } else if (activeField === 'password') {
            setData('password', result);
            setVoiceFeedback('Password has been set');
        } else {
            // Process voice commands
            const feedback = processLoginCommand(result, setData);
            setVoiceFeedback(feedback);
        }
        
        // Clear feedback after 3 seconds
        setTimeout(() => setVoiceFeedback(''), 3000);
    };
    
    // Handle keyboard shortcuts for voice input
    useEffect(() => {
        const handleKeyDown = (e) => {
            // Alt+V for email field
            if (e.altKey && e.key === 'v') {
                e.preventDefault();
                setActiveField('email');
                document.querySelector('#email-voice-button')?.click();
            }
            // Alt+P for password field
            if (e.altKey && e.key === 'p') {
                e.preventDefault();
                setActiveField('password');
                document.querySelector('#password-voice-button')?.click();
            }
        };
        
        window.addEventListener('keydown', handleKeyDown);
        return () => window.removeEventListener('keydown', handleKeyDown);
    }, []);

    const submit = (e) => {
        e.preventDefault();

        post(route('login'), {
            onFinish: () => reset('password'),
        });
    };

    return (
        <GuestLayout>
            <Head title="Log in" />

            {status && (
                <div className="mb-4 text-sm font-medium text-green-600">
                    {status}
                </div>
            )}

            <form onSubmit={submit}>
                <div>
                    <InputLabel htmlFor="email" value="Email" />
                    <div className="relative">
                        <TextInput
                            id="email"
                            type="email"
                            name="email"
                            value={data.email}
                            className="mt-1 block w-full pr-10"
                            autoComplete="username"
                            isFocused={true}
                            onChange={(e) => setData('email', e.target.value)}
                            onFocus={() => setActiveField('email')}
                            placeholder="Or say: 'My email is...'"
                        />
                        <div className="absolute inset-y-0 right-0 flex items-center pr-3 space-x-1">
                            <span className="text-xs text-gray-400 hidden sm:inline">Alt+V</span>
                            <VoiceInput 
                                id="email-voice-button"
                                onResult={handleVoiceResult}
                                disabled={processing}
                                className={activeField === 'email' ? 'text-blue-500' : 'text-gray-400'}
                            />
                        </div>
                    </div>
                    <InputError message={errors.email} className="mt-2" />
                </div>

                <div className="mt-4">
                    <InputLabel htmlFor="password" value="Password" />
                    <div className="relative">
                        <TextInput
                            id="password"
                            type="password"
                            name="password"
                            value={data.password}
                            className="mt-1 block w-full pr-10"
                            autoComplete="current-password"
                            onChange={(e) => setData('password', e.target.value)}
                            onFocus={() => setActiveField('password')}
                            placeholder="Or say: 'My password is...'"
                        />
                        <div className="absolute inset-y-0 right-0 flex items-center pr-3 space-x-1">
                            <span className="text-xs text-gray-400 hidden sm:inline">Alt+P</span>
                            <VoiceInput 
                                id="password-voice-button"
                                onResult={handleVoiceResult}
                                disabled={processing}
                                className={activeField === 'password' ? 'text-blue-500' : 'text-gray-400'}
                            />
                        </div>
                    </div>
                    <InputError message={errors.password} className="mt-2" />
                </div>

                <div className="mt-4 block">
                    <label className="flex items-center">
                        <Checkbox
                            name="remember"
                            checked={data.remember}
                            onChange={(e) =>
                                setData('remember', e.target.checked)
                            }
                        />
                        <span className="ms-2 text-sm text-gray-600">
                            Remember me
                        </span>
                    </label>
                </div>

                <div className="mt-4 flex items-center justify-end">
                    {canResetPassword && (
                        <Link
                            href={route('password.request')}
                            className="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                        >
                            Forgot your password?
                        </Link>
                    )}

                    <div className="relative">
                        <PrimaryButton 
                            id="login-button"
                            className="w-full justify-center" 
                            disabled={processing}
                        >
                            Log in {processing ? '...' : ''}
                        </PrimaryButton>
                        {voiceFeedback && (
                            <div className="absolute -top-8 left-0 right-0 text-center">
                                <div className="inline-block bg-blue-500 text-white text-xs px-2 py-1 rounded">
                                    {voiceFeedback}
                                </div>
                            </div>
                        )}
                    </div>
                </div>
            </form>

            {/* Social Login Buttons */}
            <div className="mt-6">
                <div className="mt-6 text-center">
                    <p className="text-sm text-gray-500 mb-4">
                        Try voice commands: <br className="sm:hidden" />
                        <span className="hidden sm:inline">•</span> "My email is example@test.com"<br className="sm:hidden" />
                        <span className="hidden sm:inline">•</span> "My password is mypassword123"<br className="sm:hidden" />
                        <span className="hidden sm:inline">•</span> "Remember me" or "Login"
                    </p>
                </div>
                
                <div className="relative my-6">
                    <div className="absolute inset-0 flex items-center">
                        <div className="w-full border-t border-gray-300" />
                    </div>
                    <div className="relative flex justify-center text-sm">
                        <span className="px-2 bg-white text-gray-500">Or continue with</span>
                    </div>
                </div>

                <div className="mt-6 grid grid-cols-3 gap-3">
                    {/* GitHub */}
                    <button
                        type="button"
                        onClick={() => router.get(route('social.redirect', 'github'))}
                        className="inline-flex w-full justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    >
                        <FaGithub className="h-5 w-5" />
                        <span className="sr-only">Sign in with GitHub</span>
                    </button>

                    {/* Google */}
                    <button
                        type="button"
                        onClick={() => router.get(route('social.redirect', 'google'))}
                        className="inline-flex w-full justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    >
                        <FaGoogle className="h-5 w-5 text-red-500" />
                        <span className="sr-only">Sign in with Google</span>
                    </button>

                    {/* Apple */}
                    <button
                        type="button"
                        onClick={() => router.get(route('social.redirect', 'apple'))}
                        className="inline-flex w-full justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    >
                        <FaApple className="h-5 w-5 text-gray-900" />
                        <span className="sr-only">Sign in with Apple</span>
                    </button>
                </div>
            </div>
        </GuestLayout>
    );
}
