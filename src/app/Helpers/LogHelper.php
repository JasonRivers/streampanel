<?php
namespace App\Helpers;

// Externals
use Illuminate\Database\Eloquent\Model;

// Facades
use Log;

/**
 * Helper for logging against a model.
 * 
 * @method void debug(string $message, array $content = [])
 * @method void info(string $message, array $content = [])
 * @method void notice(string $message, array $content = [])
 * @method void warning(string $message, array $content = [])
 * @method void error(string $message, array $content = [])
 * @method void critical(string $message, array $content = [])
 * @method void alert(string $message, array $content = [])
 * @method void emergency(string $message, array $content = [])
 */
class LogHelper
{
    protected $subject;
    
    
    /**
     * Create a new log helper for a model.
     * 
     * @param Model $subject
     */
    public function __construct(Model $subject)
    {
        $this->subject = $subject;
    }
    
    /**
     * Log a message at the specified level.
     * 
     * @param string $level Level for the log entry
     * @param string $message The log message
     * @param array $context Context for the log message
     */
    public function log($level, $message, $context = [])
    {
        Log::log($level, "{$this->subject} {$message}", $context);
    }
    
    public function __call($name, $args)
    {
        switch ($name) {
            case 'debug':
            case 'info':
            case 'notice':
            case 'warning':
            case 'error':
            case 'critical':
            case 'alert':
            case 'emergency':
                $message = '';
                $context = [];
                
                if (isset($args[0])) {
                    $message = $args[0];
                }
                if (isset($args[1])) {
                    $context = $args[1];
                }
                $this->log($name, $message, $context);
                break;
            default:
                break;
        }
    }
}