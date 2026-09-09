import { onScopeDispose, shallowRef } from 'vue';

/**
 * Lets the page decide what the shell's FAB does.
 *
 * The bottom-nav FAB and the desktop rail's CTA live in the layout, but what
 * "add" means depends on the screen — open the quick-add sheet for this
 * vehicle, or send someone with an empty garage to add their first car. A page
 * claims the button while it is mounted; if none has, the shell falls back to
 * saying the feature is not available here.
 */
const handler = shallowRef<(() => void) | null>(null);

export function useQuickAdd() {
    /**
     * Claim the FAB for as long as the calling component is alive.
     */
    function claimQuickAdd(fn: () => void): void {
        handler.value = fn;

        onScopeDispose(() => {
            if (handler.value === fn) {
                handler.value = null;
            }
        });
    }

    function triggerQuickAdd(): boolean {
        if (handler.value === null) {
            return false;
        }

        handler.value();

        return true;
    }

    return { claimQuickAdd, triggerQuickAdd };
}
