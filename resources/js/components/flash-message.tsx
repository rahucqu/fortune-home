import { type SharedData } from '@/types';
import { usePage } from '@inertiajs/react';
import { useEffect } from 'react';
import { toast } from 'sonner';

export function FlashMessage() {
    const { flashMessages } = usePage<SharedData>().props;

    useEffect(() => {
        const toastOptions = { position: 'bottom-center' as const };
        const timeoutId = setTimeout(() => {
            Object.entries(flashMessages).forEach(([type, message]) => {
                if (message && message.trim()) {
                    switch (type) {
                        case 'success':
                            toast.success(message, toastOptions);
                            break;
                        case 'error':
                            toast.error(message, toastOptions);
                            break;
                        case 'warning':
                            toast.warning(message, toastOptions);
                            break;
                        case 'info':
                            toast.info(message, toastOptions);
                            break;
                        default:
                            toast(message, toastOptions);
                    }
                }
            });
        }, 500);
        return () => clearTimeout(timeoutId);
    }, [flashMessages]);

    return null;
}
