# Advance File Manager

🛠️ **Advance File Manager** is a secure, web-based PHP file management tool designed for efficient file and directory operations with a sleek, neon-green interface. Built with security and usability in mind, it offers a powerful command shell, file editing, and navigation features.

🦁 **MAD TIGER**  
📩 Telegram: [@DevidLuice](https://t.me/DevidLuice)

![alt text](photo.png)

## Features

- **File Management**:
  - Upload, download, edit, rename, and delete files.
  - Create new files and directories.
  - View file sizes and permissions.

- **Directory Navigation**:
  - Clickable directory path for quick navigation to parent directories.
  - Browse directories with a clean, table-based interface.

- **Command Shell**:
  - Execute shell commands with a toggleable panel (show/hide via button).
  - Preset commands (e.g., `ls -al`, `whoami`, `pwd`) for quick access.
  - Command history with clickable entries to reuse past commands.
  - Option to clear command history.

- **Security**:
  - Password-protected access with session-based authentication.
  - Secure logout mechanism (`?logout=1`).
  - Bot detection to block crawlers and scanners.
  - Dynamic permission changes to secure the script itself.
  - HTTP headers for enhanced security (e.g., CSP, X-Frame-Options).
  - Cookie consent for session handling.

- **System Information**:
  - Displays client/server IP, server OS, software, and certificate status.

- **User Interface**:
  - Neon-green, cyberpunk-inspired design with animations.
  - Real-time clock display.
  - Responsive and intuitive layout with hover effects.

## Installation

1. **Prerequisites**:
   - PHP 7.4 or higher.
   - Web server (e.g., Apache, Nginx) with PHP support.
   - Write permissions for the directory where the script is installed.
   - `shell_exec` enabled in `php.ini` for command shell functionality.

2. **Setup**:
   ```bash
   git clone https://github.com/mrTr1cky/FileManager.git
   cd FileManager
   ```

3. **Configuration**:
   - Open `index.php` and update the `$password` variable:
     ```php
     $password = "your_secure_password"; // Change this!
     ```
   - Ensure the directory is writable by the web server (e.g., `chmod 755` or `777` for testing, then secure it).

4. **Deploy**:
   - Upload `index.php` to your web server.
   - Access the script via your browser (e.g., `http://yourdomain.com/index.php`).

5. **Security**:
   - Change the default password immediately.
   - Restrict access to the script (e.g., via `.htaccess` or server configuration).
   - Check `php_errors.log` for any issues after deployment.

## Usage

1. **Login**:
   - Enter the configured password to access the file manager.
   - Accept the cookie consent prompt for session handling.

2. **File Operations**:
   - Use the upload form to add files.
   - Create files or folders via the create form.
   - Click file names to navigate directories or perform actions (edit, download, rename, delete).

3. **Command Shell**:
   - Toggle the command shell panel using the "Toggle Command Shell" button.
   - Select preset commands or enter custom ones.
   - Click command history entries to reuse commands.
   - Clear history with the "Clear History" button.

4. **Navigation**:
   - Click segments in the directory path (e.g., `/home/username/public_html/`) to jump to parent directories.

5. **Logout**:
   - Click the "Logout" link to end the session.

## Security Notes

- **Password**: Always set a strong, unique password in `$password`.
- **Permissions**: Ensure the script file (`index.php`) has restrictive permissions (e.g., `0600`).
- **Shell Exec**: The command shell uses `shell_exec`, which can be dangerous if not restricted. Disable it in `php.ini` (`disable_functions`) if not needed.
- **Error Logging**: Check `php_errors.log` for issues. Ensure the log file is not publicly accessible.
- **HTTPS**: Use HTTPS to secure session cookies and data transmission.
- **Access Control**: Restrict access to trusted IPs or use additional authentication layers.

## Debugging

If you encounter an HTTP 500 error:
1. Check `php_errors.log` in the script’s directory or the server’s default log location.
2. Verify:
   - Directory permissions (writable by the web server).
   - `shell_exec` availability (`function_exists('shell_exec')`).
   - Session storage path (`session.save_path` in `php.ini`).
3. Use the "Debug Information" section (visible after login) to check:
   - PHP version.
   - Session ID.
   - Current directory.
   - Shell exec status.

## Contributing

Contributions are welcome! Please:
1. Fork the repository.
2. Create a feature branch (`git checkout -b feature/your-feature`).
3. Commit changes (`git commit -m 'Add your feature'`).
4. Push to the branch (`git push origin feature/your-feature`).
5. Open a pull request.

## License

This project is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.

## Contact

For support or inquiries, contact:  
🦁 **MAD TIGER**  
📩 Telegram: [@DevidLuice](https://t.me/DevidLuice)
