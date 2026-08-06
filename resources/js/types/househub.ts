/**
 * Mirrors the `App\Data\*` DTOs. Keep in step with them — they are the contract
 * between the use cases and these screens.
 */

export interface Member {
    id: number;
    name: string;
    initials: string;
    /** A `var(--hh-*)` reference, so it follows the active theme. */
    colour: string;
    status: string | null;
}

export interface ShoppingItem {
    id: number;
    name: string;
    quantity: string | null;
    category: string;
    done: boolean;
}

export interface ShoppingList {
    id: number;
    name: string;
    slug: string;
    colour: string;
    remaining: number;
    total: number;
    items: ShoppingItem[];
}

export interface ShoppingGroup {
    label: string;
    items: ShoppingItem[];
}

export interface Chore {
    id: number;
    name: string;
    status: ChoreStatus;
    done: boolean;
    dueLabel: string | null;
    repeatLabel: string | null;
    assignee: Member | null;
}

export type ChoreStatus = 'today' | 'upcoming' | 'done' | 'recurring';

export interface ChoreColumn {
    status: ChoreStatus;
    title: string;
    count: number;
    items: Chore[];
}

export interface ChoreProgress {
    done: number;
    total: number;
    percentage: number;
}

export interface MemberScore {
    member: Member;
    done: number;
    total: number;
    percentage: number;
}

export interface PlannedMeal {
    id: number;
    name: string;
    slot: string;
    duration: string | null;
    difficulty: string | null;
    description: string | null;
    /** Index into the `--hh-t1` … `--hh-t5` gradient tokens. */
    tint: number;
    missingIngredients: number;
    missingLabel: string;
    cook: Member | null;
}

export interface PlannerDay {
    date: string;
    dayLabel: string;
    dateLabel: string;
    isToday: boolean;
    meals: PlannedMeal[];
}

export interface Recipe {
    id: number;
    name: string;
    description: string | null;
    meta: string;
    tint: number;
    tags: string[];
}

export interface AgendaEntry {
    id: number;
    title: string;
    time: string;
    who: string | null;
    colour: string;
}

export interface AgendaGroup {
    label: string;
    items: AgendaEntry[];
}

export interface BudgetCategory {
    id: number;
    label: string;
    colour: string;
    spent: string;
    percentageOfBudget: number;
    shareOfTotal: number;
}

export interface BudgetSummary {
    monthLabel: string;
    spent: string;
    budgeted: string;
    daysLeft: number;
    categories: BudgetCategory[];
}

export interface Weather {
    location: string;
    temperature: string;
    summary: string;
}

export interface Dashboard {
    greeting: string;
    dateLine: string;
    summaryLine: string;
    streakDays: number;
    family: Member[];
    weather: Weather | null;
    tonight: PlannedMeal | null;
    shoppingList: ShoppingList | null;
    chores: Chore[];
    choreProgress: ChoreProgress;
    agenda: AgendaGroup[];
    budget: BudgetSummary;
}
