<?php
/**
 * Logging System
 * Provides structured logging for the application
 */

class Logger {
    private static $logPath;
    private static $logLevel;
    private static $levels = [
        'DEBUG' => 0,
        'INFO' => 1,
        'WARNING' => 2,
        'ERROR' => 3,
        'CRITICAL' => 4
    ];
    
    public static function init() {
        try {
            self::$logPath = defined('LOG_PATH') ? LOG_PATH : __DIR__ . '/../logs';
            self::$logLevel = self::$levels[strtoupper(defined('LOG_LEVEL') ? LOG_LEVEL : 'INFO')] ?? self::$levels['INFO'];
            
            // Ensure log directory exists
            if (!is_dir(self::$logPath)) {
                @mkdir(self::$logPath, 0755, true);
            }
        } catch (Exception $e) {
            // If initialization fails, set defaults
            self::$logPath = null;
            self::$logLevel = self::$levels['INFO'];
        }
    }
    
    /**
     * Write log entry
     */
    private static function write($level, $message, $context = []) {
        try {
            // Initialize if not done
            if (self::$logPath === null) {
                self::init();
            }
            
            $levelCode = self::$levels[$level] ?? self::$levels['INFO'];
            if (self::$logLevel !== null && $levelCode < self::$logLevel) {
                return;
            }
            
            $timestamp = date('Y-m-d H:i:s');
            $contextStr = !empty($context) ? ' ' . json_encode($context) : '';
            $logEntry = "[{$timestamp}] [{$level}] {$message}{$contextStr}\n";
            
            // Try to write to log file, but don't fail if it doesn't work
            if (self::$logPath && is_dir(self::$logPath) && is_writable(self::$logPath)) {
                $logFile = self::$logPath . '/app_' . date('Y-m-d') . '.log';
                @file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
            }
            
            // Also log errors to PHP error log
            if ($levelCode >= self::$levels['ERROR']) {
                @error_log($message . $contextStr);
            }
        } catch (Exception $e) {
            // Silently fail - don't break the application if logging fails
            @error_log('Logger error: ' . $e->getMessage());
        }
    }
    
    public static function debug($message, $context = []) {
        self::write('DEBUG', $message, $context);
    }
    
    public static function info($message, $context = []) {
        self::write('INFO', $message, $context);
    }
    
    public static function warning($message, $context = []) {
        self::write('WARNING', $message, $context);
    }
    
    public static function error($message, $context = []) {
        self::write('ERROR', $message, $context);
    }
    
    public static function critical($message, $context = []) {
        self::write('CRITICAL', $message, $context);
    }
}

// Initialize logger
Logger::init();

