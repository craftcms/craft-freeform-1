import type { Layout, Row } from '@editor/builder/types/layout';
import type { RootState } from '@editor/store';
import type { SaveSubscriber } from '@editor/store/middleware/state-persist';
import { TOPIC_SAVE } from '@editor/store/middleware/state-persist';
import type { PayloadAction } from '@reduxjs/toolkit';
import { createSlice } from '@reduxjs/toolkit';

type RowState = Row[];

type SwapPayload = {
  currentUid: string;
  targetUid: string;
};

const initialState: RowState = [];

export const rowsSlice = createSlice({
  name: 'rows',
  initialState,
  reducers: {
    add: (state, action: PayloadAction<{ layoutUid: string; uid: string }>) => {
      const { layoutUid, uid } = action.payload;
      const highestOrder =
        Math.max(
          ...state
            .filter((row) => row.layoutUid === layoutUid)
            .map((row) => row.order)
        ) ?? -1;

      state.push({
        uid,
        order: highestOrder + 1,
        layoutUid,
      });
    },
    remove: (state, action: PayloadAction<string>) => {
      state = state.filter((row) => row.uid !== action.payload);
    },
    swap: (state, action: PayloadAction<SwapPayload>) => {
      const current = state.find(
        (row) => row.uid === action.payload.currentUid
      );
      const target = state.find((row) => row.uid === action.payload.targetUid);

      const tempOrder = current.order;
      current.order = target.order;
      target.order = tempOrder;
    },
  },
});

export const { swap, add, remove } = rowsSlice.actions;

export const selectRowsInLayout =
  (layout: Layout | undefined) =>
  (state: RootState): Row[] =>
    layout
      ? state.rows
          .filter((row) => row.layoutUid === layout.uid)
          .sort((a, b) => a.order - b.order)
      : [];

export default rowsSlice.reducer;

const persist: SaveSubscriber = (_, data) => {
  const { state, persist } = data;

  persist.rows = state.rows;
};

PubSub.subscribe(TOPIC_SAVE, persist);
