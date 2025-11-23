export function processLoginCommand(command, setData) {
    const lowerCommand = command.toLowerCase().trim();
    
    // Fill email
    if (lowerCommand.startsWith('my email is') || lowerCommand.startsWith('email is') || lowerCommand.startsWith('email')) {
        const email = command.split('is').pop().trim();
        if (email.includes('@') && email.includes('.')) {
            setData('email', email);
            return `Email set to ${email}`;
        }
    }
    
    // Fill password
    if (lowerCommand.startsWith('my password is') || lowerCommand.startsWith('password is') || lowerCommand.startsWith('password')) {
        const password = command.split('is').pop().trim();
        if (password.length > 0) {
            setData('password', password);
            return 'Password has been set';
        }
    }
    
    // Toggle remember me
    if (lowerCommand.includes('remember me')) {
        setData('remember', !lowerCommand.includes('not'));
        return `Remember me ${!lowerCommand.includes('not') ? 'enabled' : 'disabled'}`;
    }
    
    // Submit form
    if (lowerCommand.includes('submit') || lowerCommand.includes('login') || lowerCommand.includes('log in')) {
        document.getElementById('login-button')?.click();
        return 'Submitting login form';
    }
    
    // Clear form
    if (lowerCommand.includes('clear') || lowerCommand.includes('reset')) {
        setData('email', '');
        setData('password', '');
        setData('remember', false);
        return 'Form has been cleared';
    }
    
    return `I didn't understand that command. Try saying "My email is..." or "My password is..."`;
}
