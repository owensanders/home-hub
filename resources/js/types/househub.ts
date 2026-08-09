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
    /** Raw `ShoppingCategory` enum value, e.g. 'fresh'. */
    category: string;
    done: boolean;
}

export interface ShoppingList {
    id: number;
    name: string;
    slug: string;
    colour: string;
    /** The `Palette` case name, for the colour picker. */
    colourKey: string;
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
    recipeId: number;
    name: string;
    slot: string;
    /** Raw enum value ('breakfast' | 'lunch' | 'dinner'), for form <select>s. */
    slotKey: string;
    /** Y-m-d, for form inputs. */
    plannedOn: string;
    duration: string | null;
    difficulty: string | null;
    description: string | null;
    tags: string[];
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
    durationLabel: string | null;
    difficulty: string | null;
    tint: number;
    tags: string[];
    isFavourite: boolean;
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

export interface CalendarEvent {
    id: number;
    title: string;
    date: string;
    /** 'All day', '18:00', or '18:00–19:30'. */
    time: string;
    /** Attendee first names, or 'Everyone' for a whole-house event. */
    who: string;
    isAllDay: boolean;
    location: string | null;
    notes: string | null;
    colour: string;
    /** The `Palette` case name, for the colour picker. */
    colourKey: string;
    attendees: Member[];
    /** `YYYY-MM-DDTHH:mm`, for prefilling the edit form. */
    startsAt: string;
    endsAt: string | null;
}

export interface CalendarDay {
    date: string;
    dayLabel: string;
    dateLabel: string;
    isToday: boolean;
    isCurrentMonth: boolean;
    events: CalendarEvent[];
}

export interface CalendarMonth {
    /** `YYYY-MM`. */
    month: string;
    monthLabel: string;
    previousMonth: string;
    nextMonth: string;
    today: string;
    weekdayLabels: string[];
    /** Always 42 — six rows of seven. */
    days: CalendarDay[];
    members: Member[];
}

export interface BudgetCategory {
    id: number;
    label: string;
    icon: string;
    colour: string;
    spent: string;
    budgeted: string;
    budgetedPence: number;
    spentPence: number;
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

export interface BudgetTransaction {
    id: number;
    label: string;
    amount: string;
    amountPence: number;
    categoryId: number;
    categoryLabel: string;
    categoryColour: string;
    meta: string;
}

export interface IncomeSource {
    id: number;
    label: string;
    meta: string | null;
    colour: string;
    amount: string;
    amountPence: number;
    initials: string;
}

export interface BudgetCategoryOption {
    id: number;
    label: string;
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
