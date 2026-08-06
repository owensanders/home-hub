/** The five card gradients from the design, cycled by index. */
export function tint(index: number): string {
    return `var(--hh-t${(index % 5) + 1})`;
}

/** Conic-gradient sweep for the progress dials. */
export function dial(percentage: number, colour = 'var(--hh-mint)'): string {
    return `conic-gradient(${colour} ${percentage * 3.6}deg, var(--hh-sunk) 0)`;
}

/** Fill for a tick box — mint when ticked, hairline outline when not. */
export function tickStyle(done: boolean): { borderColor: string; background: string } {
    return {
        borderColor: done ? 'var(--hh-mint)' : 'var(--hh-line)',
        background: done ? 'var(--hh-mint)' : 'transparent',
    };
}
