<template>
  <main class="container">
    <h1>Mini Task Manager</h1>

    <!-- Add -->
    <form class="row" @submit.prevent="createTask">
      <input v-model="newTitle" placeholder="New task title" required />
      <button :disabled="loadingCreate">Add</button>
    </form>

    <section class="boards">
      <!-- Pending -->
      <div class="board">
        <header><h2>Pending</h2><small>{{ pending.length }}</small></header>
        <draggable v-model="pending" group="tasks" item-key="id" @end="onDragEnd">
          <template #item="{ element }">
            <article class="task">
              <span>{{ element.title }}</span>
              <div class="actions">
                <button class="soft" @click="toggle(element)">✓</button>
                <button class="danger" @click="remove(element)">✕</button>
              </div>
            </article>
          </template>
        </draggable>
      </div>

      <!-- Done -->
      <div class="board">
        <header><h2>Done</h2><small>{{ done.length }}</small></header>
        <draggable v-model="done" group="tasks" item-key="id" @end="onDragEnd">
          <template #item="{ element }">
            <article class="task done">
              <span>{{ element.title }}</span>
              <div class="actions">
                <button class="soft" @click="toggle(element)">↩</button>
                <button class="danger" @click="remove(element)">✕</button>
              </div>
            </article>
          </template>
        </draggable>
      </div>
    </section>
  </main>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import draggable from 'vuedraggable'
import { api } from './api'

const tasks = ref([])
const loadingCreate = ref(false)
const newTitle = ref('')

const pending = computed({
  get: () => tasks.value.filter(t => t.status === 'pending'),
  set: () => {} 
})
const done = computed({
  get: () => tasks.value.filter(t => t.status === 'done'),
  set: () => {}
})

async function fetchAll() {
  const { data } = await api.get('/tasks')
  tasks.value = data
}

async function createTask() {
  loadingCreate.value = true
  try {
    const { data } = await api.post('/tasks', { title: newTitle.value, status: 'pending' })
    tasks.value.unshift(data)
    newTitle.value = ''
  } finally {
    loadingCreate.value = false
  }
}

async function remove(t) {
  const keep = tasks.value
  tasks.value = tasks.value.filter(x => x.id !== t.id)
  try { await api.delete(`/tasks/${t.id}`) } catch { tasks.value = keep }
}

async function toggle(t) {
  const next = t.status === 'done' ? 'pending' : 'done'
  const old  = t.status
  t.status = next
  try {
    const { data } = await api.put(`/tasks/${t.id}`, { status: next })
    Object.assign(t, data)
  } catch { t.status = old }
}

async function onDragEnd() {
  const updates = []
  for (const t of tasks.value) {
    const shouldBe = pending.value.find(x => x.id === t.id) ? 'pending' : 'done'
    if (t.status !== shouldBe) {
      const old = t.status
      t.status = shouldBe
      updates.push(
        api.put(`/tasks/${t.id}`, { status: shouldBe })
           .then(({ data }) => Object.assign(t, data))
           .catch(() => { t.status = old })
      )
    }
  }
  await Promise.all(updates)
}

onMounted(fetchAll)
</script>

<style>
:root { color-scheme: light; }
* { box-sizing: border-box; }
body { margin:0; background:#f6f7fb; }
.container { max-width: 960px; margin: 2rem auto; padding: 0 1rem; font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial; }
h1 { margin: 0 0 1rem; }
.row { display:flex; gap:.5rem; margin-bottom: 1rem; }
input { flex:1; padding:.6rem .8rem; border:1px solid #d9dbe1; border-radius:.5rem; }
button { padding:.6rem .9rem; border:0; border-radius:.5rem; background:#1f6feb; color:#fff; cursor:pointer; }
button.soft { background:#e9eefc; color:#1f6feb; }
button.danger { background:#ef4444; }
.boards { display:grid; grid-template-columns:1fr 1fr; gap:1rem; }
.board { background:#fff; border:1px solid #ececf0; border-radius:.8rem; padding:1rem; min-height:340px; }
.board header { display:flex; align-items:center; justify-content:space-between; margin-bottom:.5rem; }
.board small { color:#6b7280; }
.task { display:flex; align-items:center; justify-content:space-between; background:#fafbff; border:1px solid #ececf0; border-radius:.6rem; padding:.6rem .8rem; margin:.45rem 0; }
.task.done { opacity:.9; text-decoration: line-through; background:#f5fff6; }
.actions { display:flex; gap:.4rem; }
</style>

<script>
export default { components: { draggable } }
</script>
