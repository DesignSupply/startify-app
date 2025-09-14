import type { InjectionKey } from 'vue';
import type { Store } from 'pinia';

type stateType = { message: string };
type gettersType = { getMessage: string };
type actionsType = { updateMessage: (payload: string) => void };

export type storeType = Store<'useStore', stateType, gettersType, actionsType>;
export const useStoreKey: InjectionKey<storeType> = Symbol('useStore');
